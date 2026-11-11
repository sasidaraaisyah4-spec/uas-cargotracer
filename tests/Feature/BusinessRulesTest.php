<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test State Machine disallows status change from terminal DELIVERED state
     */
    public function test_state_machine_prevents_transition_from_terminal_delivered_state()
    {
        $deliveredShipment = Shipment::where('status', 'delivered')->first();
        $operator = User::where('email', 'operator.jkt@cargotrace.test')->first();

        $shipmentService = app(ShipmentService::class);

        $this->expectException(InvalidArgumentException::class);
        $shipmentService->transitionStatus($deliveredShipment, 'in_transit', $operator);
    }

    /**
     * Test Tracking Event requires chronological sequence (cannot go backward in time)
     */
    public function test_tracking_event_prevents_backward_timestamp_sequence()
    {
        $shipment = Shipment::where('status', 'in_transit')->first();
        $operator = User::where('email', 'operator.jkt@cargotrace.test')->first();

        $trackingService = app(TrackingService::class);

        $this->expectException(InvalidArgumentException::class);
        $trackingService->addTrackingEvent($shipment, $operator, [
            'event_title' => 'Invalid Past Event',
            'occurred_at' => now()->subYears(2), // 2 years in past
        ]);
    }

    /**
     * Test EtaService calculates deterministic delay and severity according to thresholds
     */
    public function test_eta_service_deterministic_delay_calculation()
    {
        $etaService = app(EtaService::class);

        $shipment = Shipment::where('status', 'in_transit')->first();

        // 1. Expected arrival is today 10:00, Estimated arrival is today 14:00 (4h delay -> LOW)
        $shipment->expected_arrival_at = Carbon::now();
        $shipment->estimated_delivery_at = Carbon::now()->addHours(4);
        $resLow = $etaService->evaluateDelay($shipment);

        $this->assertEquals(4.00, $resLow['delay_hours']);
        $this->assertEquals('LOW', $resLow['delay_severity']);
        $this->assertFalse($resLow['is_delayed']); // Threshold is 12h

        // 2. Expected arrival is today, Estimated arrival is +30h (30h delay -> HIGH & is_delayed = true)
        $shipment->estimated_delivery_at = Carbon::now()->addHours(30);
        $resHigh = $etaService->evaluateDelay($shipment);

        $this->assertEquals(30.00, $resHigh['delay_hours']);
        $this->assertEquals('HIGH', $resHigh['delay_severity']);
        $this->assertTrue($resHigh['is_delayed']);
    }
}

