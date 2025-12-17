<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErrorLogger
{
    /**
     * Log error to remote Node.js error management system
     */
    public static function logToRemoteSystem($errorMessage, $clientName = null, $clientId = null, $timingData = null)
    {
        try {
            $apiUrl = config('services.error_system.url', 'https://api-timora-ems.timora.ph');
            $apiKey = config('services.error_system.api_key', '8be98cf36dc1b22e5a25b5523c04e8925501ecd3c3a9da5fcee01823d2a41c34');
            
            // Debug: Log what we're sending
            Log::debug('Preparing to send error to remote system', [
                'error_message' => substr($errorMessage, 0, 100),
                'client_name' => $clientName,
                'client_id' => $clientId,
                'timing_data' => $timingData,
                'api_url' => $apiUrl
            ]);
            
            // Prepare the payload
            $payload = [
                'error_message' => substr($errorMessage, 0, 65535), // Ensure it fits in TEXT field
            ];
            
            // Add client_name if provided
            if ($clientName) {
                $payload['client_name'] = $clientName;
            }
            
            // Add client_id if provided
            if ($clientId) {
                $payload['client_id'] = $clientId;
            }
            
            // Add timing data as separate fields if provided
            if ($timingData && is_array($timingData)) {
                // Add only server_processing_time_ms and ttfb_estimate_ms
                if (isset($timingData['server_processing_time_ms'])) {
                    $payload['server_processing_time_ms'] = $timingData['server_processing_time_ms'];
                }
                if (isset($timingData['ttfb_estimate_ms'])) {
                    $payload['ttfb_estimate_ms'] = $timingData['ttfb_estimate_ms'];
                }
                $payload['timing_data_full'] = json_encode($timingData);
            }
            
            Log::debug('Payload to send:', $payload);
            
            $response = Http::timeout(10) // Increased timeout
                ->retry(3, 100) // Retry 3 times
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl . '/api/log-error', $payload);
            
            Log::debug('Response from error system:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                Log::info('Error logged to remote system', [
                    'error_id' => $response->json('error_id'),
                    'client_id' => $response->json('client_id'),
                    'client_name' => $response->json('client_name')
                ]);
                return true;
            }
            
            Log::warning('Failed to log error to remote system', [
                'status' => $response->status(),
                'response' => $response->body(),
                'payload_sent' => $payload
            ]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('Exception when calling error management API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload_attempted' => $payload ?? []
            ]);
            return false;
        }
    }
}