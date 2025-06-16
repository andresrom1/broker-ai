<?php

namespace App\Services\AssistantFlow;

use Illuminate\Support\Facades\Cache;
use Log;

class CacheManagerService
{
    protected const VEHICLE_DATA_CACHE_PREFIX = 'vehicle_data_';
    protected const COVERAGE_DATA_CACHE_PREFIX = 'vehicle_coverage_';
    protected const CACHE_TTL_HOURS = 1; // Tiempo de vida de la caché: 1 hora

    /**
     * Almacena los datos del vehículo en caché asociados a un thread ID.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @param array $vehicleData Los datos del vehículo a almacenar.
     * @return void
     */
    public function putVehicleData(string $threadId, array $vehicleData): void
    {
        $cacheKey = self::VEHICLE_DATA_CACHE_PREFIX . $threadId;
        Cache::put($cacheKey, $vehicleData, now()->addHours(self::CACHE_TTL_HOURS));
        Log::info('Datos del vehículo almacenados en caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
    }
    
    /**
     * Almacena los datos de la cobertura en caché asociados a un thread ID.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @param string $coverageData Los datos del vehículo a almacenar.
     * @return void
     */
    public function putCoverageData(string $threadId, string $coverageData): void
    {
        $cacheKey = self::COVERAGE_DATA_CACHE_PREFIX . $threadId;
        Cache::put($cacheKey, $coverageData, now()->addHours(self::CACHE_TTL_HOURS));
        Log::info('Datos de la cobertura almacenados en caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
    }

    /**
     * Recupera los datos del vehículo de la caché asociados a un thread ID.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @return array|null Los datos del vehículo si se encuentran, o null si no.
     */
    public function getVehicleData(string $threadId): ?array
    {
        $cacheKey = self::VEHICLE_DATA_CACHE_PREFIX . $threadId;
        $data = Cache::get($cacheKey);
        
        if ($data) {
            Log::info('Datos del vehículo recuperados de caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
            return $data;
        }
        
        Log::warning('Datos del vehículo no encontrados en caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
        return null;
    }
    
    /**
     * Recupera los datos de la cobertura de la caché asociados a un thread ID.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @return array|null Los datos de la cobertura si se encuentran, o null si no.
     */
    public function getCoverageData(string $threadId): mixed
    {
        $cacheKey = self::COVERAGE_DATA_CACHE_PREFIX . $threadId;
        $data = Cache::get($cacheKey);
        
        if ($data) {
            Log::info('Datos de la cobertura recuperados de caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
            return $data;
        }
        
        Log::warning('Datos de la cobertura no encontrados en caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
        return null;
    }
    

    /**
     * Elimina los datos del vehículo de la caché asociados a un thread ID.
     * Útil para limpiar la caché al final de una conversación o en caso de error.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @return void
     */
    public function forgetVehicleData(string $threadId): void
    {
        $cacheKey = self::VEHICLE_DATA_CACHE_PREFIX . $threadId;
        Cache::forget($cacheKey);
        Log::info('Datos del vehículo eliminados de caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
    }
    
    /**
     * Elimina los datos de la cobertura de la caché asociados a un thread ID.
     * Útil para limpiar la caché al final de una conversación o en caso de error.
     *
     * @param string $threadId El ID del thread de OpenAI.
     * @return void
     */
    public function forgetCoverageData(string $threadId): void
    {
        $cacheKey = self::COVERAGE_DATA_CACHE_PREFIX . $threadId;
        Cache::forget($cacheKey);
        Log::info('Datos de la cobertura eliminados de caché', ['thread_id' => $threadId, 'cache_key' => $cacheKey]);
    }
}