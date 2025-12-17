<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ErrorLogger
{
    public static function logToRemoteSystem($errorMessage, $clientName = null, $clientId = null, $timingData = null)
    {
        try {
            $apiUrl = config('services.error_system.url');
            $apiKey = config('services.error_system.api_key');

            // Always include client info keys
            $payload = [
                'error_message' => $errorMessage,
                'client_name'   => $clientName ?? 'Unknown Tenant',
                'client_id'     => $clientId,
            ];

            if ($timingData && is_array($timingData)) {
                $payload['server_processing_time_ms'] = $timingData['server_processing_time_ms'] ?? 0;
                $payload['ttfb_estimate_ms'] = $timingData['ttfb_estimate_ms'] ?? 0;
            }

            $response = Http::timeout(10)
                ->retry(3, 100)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl . '/api/log-error', $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exception when calling error management API', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
