<?php

namespace App\Traits;

trait ResponseTimingTrait
{
    /**
     * Calculate execution time and add timing metadata
     */
    protected function timedResponse($data, $status = 200, $startTime = null)
    {
        if ($startTime) {
            $totalMs = round((microtime(true) - $startTime) * 1000, 2);

            if (is_array($data)) {
                $data['timing'] = [
                    'server_processing_time_ms' => $totalMs,
                    'ttfb_estimate_ms' => $totalMs,
                ];
            }
        }

        return response()->json($data, $status);
    }

    protected function getTimingData(float $startTime = null): ?array
    {
        if (!$startTime) {
            return null;
        }
        
        $totalMs = round((microtime(true) - $startTime) * 1000, 2);
        
        return [
            'server_processing_time_ms' => $totalMs,
            'ttfb_estimate_ms' => $totalMs,
        ];
    }
}