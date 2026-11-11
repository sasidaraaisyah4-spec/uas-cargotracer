<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Evaluate ETA and calculate deterministic delay metrics
     *
     * Rules according to UAS Brief:
     * - Compare estimated_delivery_at (ETA) with expected_arrival_at
     * - Calculate delay duration in hours
     * - If delay_hours > threshold (default 12 hours from config), mark is_delayed = true
     * - Severity:
     *   0 - 6 hours  => LOW
     *   >6 - 24 hours => MEDIUM
     *   >24 hours     => HIGH
     *
     * @param Shipment $shipment
     * @return array Calculated metrics
     */
    public function evaluateDelay(Shipment $shipment): array
    {
        $expectedArrival = $shipment->expected_arrival_at;
        $estimatedDelivery = $shipment->estimated_delivery_at;

        // If either date is missing, return baseline
        if (!$expectedArrival || !$estimatedDelivery) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0.00,
                'delay_severity' => 'NONE',
            ];
        }

        $expected = Carbon::parse($expectedArrival);
        $estimated = Carbon::parse($estimatedDelivery);

        // If estimated delivery is after expected arrival, compute delay
        if ($estimated->greaterThan($expected)) {
            $diffInMinutes = $expected->diffInMinutes($estimated);
            $delayHours = round($diffInMinutes / 60, 2);
        } else {
            $delayHours = 0.00;
        }

        $thresholdHours = (float) config('cargotrace.delay_threshold_hours', 12.0);
        $isDelayed = $delayHours >= $thresholdHours;

        $severity = $this->calculateSeverity($delayHours);

        return [
            'is_delayed' => $isDelayed,
            'delay_hours' => $delayHours,
            'delay_severity' => $severity,
        ];
    }

    /**
     * Compute severity category
     *
     * @param float $delayHours
     * @return string
     */
    public function calculateSeverity(float $delayHours): string
    {
        if ($delayHours <= 0) {
            return 'NONE';
        }

        $lowMax = (float) config('cargotrace.severity_thresholds.low_max', 6.0);
        $mediumMax = (float) config('cargotrace.severity_thresholds.medium_max', 24.0);

        if ($delayHours <= $lowMax) {
            return 'LOW';
        } elseif ($delayHours <= $mediumMax) {
            return 'MEDIUM';
        } else {
            return 'HIGH';
        }
    }

    /**
     * Recalculate and update shipment model delay fields directly
     *
     * @param Shipment $shipment
     * @return Shipment
     */
    public function applyEvaluation(Shipment $shipment): Shipment
    {
        $metrics = $this->evaluateDelay($shipment);

        $shipment->update([
            'is_delayed' => $metrics['is_delayed'],
            'delay_hours' => $metrics['delay_hours'],
            'delay_severity' => $metrics['delay_severity'],
        ]);

        return $shipment;
    }
}

