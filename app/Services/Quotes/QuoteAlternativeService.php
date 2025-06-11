<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use App\Models\QuoteAlternative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\QuotesAlternativesUpdated; // Importa tu evento

class QuoteAlternativeService
{
    private QuoteRequestService $quoteRequestService;

    public function __construct(QuoteRequestService $quoteRequestService){
        $this->quoteRequestService = $quoteRequestService;
    }
    /**
     * Actualiza las alternativas de cotización para una solicitud y la marca como cotizada,
     * disparando el evento de actualización.
     *
     * @param int $quoteRequestId El ID de la QuoteRequest.
     * @param array $alternativesData El array de alternativas a guardar.
     * @return array Resultado de la operación (ej. ['success' => bool, 'message' => string])
     */
    public function updateAlternativesAndCompleteQuote(int $quoteRequestId, array $alternativesData): array
    {
        Log::info('Ingreso al servicio de QuoteAlternativesService');
        try {
            DB::beginTransaction();

            $quoteRequest = QuoteRequest::with('alternatives')->findOrFail($quoteRequestId);
            
            // Opcional: Eliminar alternativas existentes si siempre se sobrescriben
            $quoteRequest->alternatives()->delete(); 
            
            // Guardar las nuevas alternativas
            foreach ($alternativesData as $alt) {
                QuoteAlternative::create([
                    'quote_request_id' => $quoteRequestId,
                    'company'          => $alt['company'],
                    'price'            => $alt['price'],
                    'coverage'         => $alt['coverage'],
                    'observations'     => $alt['observations'] ?? null,
                ]);
            }
            
            // Marcar la solicitud como cotizada
            $this->quoteRequestService->updateStatus($quoteRequestId,true);
            
            DB::commit();

            // Disparar el evento *después* de que la transacción se ha completado
            // Asegúrate de recargar las alternativas si no lo hiciste con `with` al inicio
            $quoteRequest->load('alternatives'); // Recargar las alternativas para el evento

            $messageContent = "¡Hola! Tu cotización ha sido completada. Aquí tienes las alternativas que hemos encontrado para ti:";
            
            // Mapear las alternativas a un array plano para el broadcast
            $broadcastableAlternatives = $quoteRequest->alternatives->map(fn($alt) => [
                'company' => $alt->company,
                'price' => $alt->price,
                'coverage' => $alt->coverage,
                'observations' => $alt->observations,
            ])->toArray();

             // --- LÍNEA DE DEPURACIÓN CRUCIAL ---
            Log::info('Intentando despachar el evento QuotesAlternativesUpdated.', ['quote_request_id' => $quoteRequestId]);

            \Event::dispatch(new QuotesAlternativesUpdated(
                $quoteRequest->id,
                $broadcastableAlternatives,
                $quoteRequest->session_id, // Asegúrate de que este campo exista y esté poblado en QuoteRequest
                $messageContent
            ));

            Log::info('Alternativas guardadas, quote completado y evento de actualización disparado.', ['quote_request_id' => $quoteRequestId]);
            
            return ['success' => true, 'message' => 'Alternativas guardadas y cotización completada.'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar alternativas y completar cotización: ' . $e->getMessage(), [
                'quote_request_id' => $quoteRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Error al guardar alternativas: ' . $e->getMessage()];
        }
    }
}
