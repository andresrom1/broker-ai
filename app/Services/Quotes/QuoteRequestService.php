<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteRequestService
{
    /**
     * Almacenar una nueva solicitud de quote
     */
    public function crearSolicitud(array $data, ?string $coberturaElegida = null, $sessionId)
    {
        try {
            DB::beginTransaction();
            
            $solicitud = QuoteRequest::create([
                'vehicle_brand'      => $data['marca'],
                'vehicle_model'      => $data['modelo'],
                'vehicle_version'    => $data['version'],
                'vehicle_fuel'       => $data['gnc'],
                'vehicle_year'       => $data['year'],
                'vehicle_postal_code'=> $data['cp'],
                'coverage_type'      => $coberturaElegida,
                'session_id' => $sessionId, 

            ]);
            
            // Si hay archivos adjuntos
            if (isset($datos['attachments'])) {
                $this->procesarArchivos($solicitud, $data['attachments']);
            }
            
            // Enviar notificaciones
            //$this->enviarNotificaciones($solicitud);
            
            DB::commit();
            
            Log::info('Nueva solicitud de quote creada', [
                'quote_request_id' => $solicitud->id,
                'customer_email' => $solicitud->customer_email
            ]);
            
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