<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use App\Services\AssistantFlow\CacheManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Lead;

class QuoteRequestService
{
    protected CacheManagerService $cacheManagerService;

    public function __construct(CacheManagerService $cacheManagerService)
    {
        $this->cacheManagerService = $cacheManagerService;
    }

    /**
     * Crea una nueva solicitud de cotización o la actualiza si ya existe una pendiente para este Lead.
     *
     * @param array $data Los datos del vehículo (marca, modelo, etc.).
     * @param string $coberturaElegida El tipo de cobertura solicitado.
     * @param int $leadId El ID del Lead al que se asocia esta solicitud. Antes era sessionId.
     * @return array Resultado de la operación (ej. ['success' => bool, 'message' => string, 'data' => QuoteRequest|null])
     */
    public function crearSolicitud(array $data, ?string $coberturaElegida = null, $leadId)
    {
        Log::info(__METHOD__.__LINE__.'Intentando crear o actualizar solicitud de cotización.', [
            'lead_id' => $leadId,
            'vehicle_data' => $data,
            'coverage_type' => $coberturaElegida
        ]);

        try {
            // Asegúrate de que el Lead exista
            $lead = Lead::find($leadId);
            if (!$lead) {
                Log::error('Lead no encontrado al crear solicitud de cotización.', ['lead_id' => $leadId]);
                return ['success' => false, 'message' => 'Lead asociado no encontrado.'];
            }

            DB::beginTransaction();
                        
            $solicitud = QuoteRequest::create([
                'vehicle_brand'      => $data['marca'],
                'vehicle_model'      => $data['modelo'],
                'vehicle_version'    => $data['version'],
                'vehicle_fuel'       => $data['gnc'],
                'vehicle_year'       => $data['year'],
                'vehicle_postal_code'=> $data['cp'],
                'coverage_type'      => $coberturaElegida,
                'lead_id' => $leadId, 

            ]);
            
            // Si hay archivos adjuntos
            if (isset($datos['attachments'])) {
                $this->procesarArchivos($solicitud, $data['attachments']);
            }
            
            // Enviar notificaciones
            //$this->enviarNotificaciones($solicitud);
            
            DB::commit();
            
            Log::info(__METHOD__.__line__.'Nueva solicitud de quote creada', [
                'quote_request_id' => $solicitud->id,
            ]);
            
            // Limpiar la caché de vehicle_data y coverage_data ya que la solicitud se ha guardado en DB.
            // Asegúrate de pasar el threadId correcto aquí. El threadId está en el Lead.
            $threadId = $lead->thread_id;
            if ($threadId) {
                $this->cacheManagerService->forgetVehicleData($threadId);
                $this->cacheManagerService->forgetCoverageData($threadId);
                Log::info(__METHOD__.__LINE__.'Caché de vehículo y cobertura limpiada.', ['thread_id' => $threadId]);
            }

            return [
                'success' => true,
                'data' => $solicitud,
                'message' => 'Solicitud de quote creada exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear solicitud de quote: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Actualiza el estado de la solicitud
     * @param int $quoteRequestId El request Id que se va a actualizar
     * @param bool $status Si la cotizacion esta Cotizada, pasar true. Si esta Pendiente pasar false
     */
    
    public function updateStatus(int $quoteRequestId, bool $status):bool {
        return $quoteRequest = QuoteRequest::find($quoteRequestId)->update(['quoted' => $status]);
    }
    
    /**
     * Validar datos de la solicitud
     */
    public function validarDatos(array $datos)
    {
        $reglas = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'service_type' => 'required|string|in:web_development,mobile_app,consulting,design,other',
            'description' => 'required|string|min:10',
            'budget_range' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
        ];
        
        return validator($datos, $reglas);
    }
    
    /**
     * Procesar archivos adjuntos
     */
    private function procesarArchivos($solicitud, $archivos)
    {
        foreach ($archivos as $archivo) {
            $path = $archivo->store('quote-requests/' . $solicitud->id, 'public');
            
            $solicitud->attachments()->create([
                'filename' => $archivo->getClientOriginalName(),
                'path' => $path,
                'size' => $archivo->getSize(),
                'mime_type' => $archivo->getMimeType()
            ]);
        }
    }
    
    /**
     * Enviar notificaciones
     */
    private function enviarNotificaciones($solicitud)
    {
        // Notificar al equipo de ventas
        // Mail::to(config('app.sales_email'))->send(new NewQuoteRequestMail($solicitud));
        
        // Notificar al cliente (confirmación)
        // Mail::to($solicitud->customer_email)->send(new QuoteRequestConfirmationMail($solicitud));
        
        // Slack notification (opcional)
        // Notification::route('slack', config('services.slack.webhook'))
        //     ->notify(new NewQuoteRequestNotification($solicitud));
    }
    
    /**
     * Obtener solicitudes con filtros
     */
    public function obtenerSolicitudes($filtros = [])
    {
        $query = QuoteRequest::query();
        
        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        
        if (isset($filtros['service_type'])) {
            $query->where('service_type', $filtros['service_type']);
        }
        
        if (isset($filtros['date_from'])) {
            $query->whereDate('created_at', '>=', $filtros['date_from']);
        }
        
        if (isset($filtros['date_to'])) {
            $query->whereDate('created_at', '<=', $filtros['date_to']);
        }
        
        return $query->latest()->paginate(15);
    }
    
    /**
     * Actualizar estado de solicitud
     */
    public function actualizarEstado($id, $nuevoEstado, $notas = null)
    {
        try {
            $solicitud = QuoteRequest::findOrFail($id);
            $estadoAnterior = $solicitud->status;
            
            $solicitud->update([
                'status' => $nuevoEstado,
                'notes' => $notas
            ]);
            
            Log::info('Estado de solicitud actualizado', [
                'quote_request_id' => $id,
                'from' => $estadoAnterior,
                'to' => $nuevoEstado
            ]);
            
            return ['success' => true, 'data' => $solicitud];
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar estado'];
        }
    }
}