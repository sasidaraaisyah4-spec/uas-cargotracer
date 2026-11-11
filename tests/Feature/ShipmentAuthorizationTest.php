<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShipmentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Customer can only view their own shipments
     */
    public function test_customer_can_only_view_own_shipment()
    {
        $customer1 = User::where('email', 'customer.siti@example.com')->first();
        $customer2 = User::where('email', 'customer.john@globaltrade.test')->first();

        $shipmentCustomer2 = Shipment::where('user_id', $customer2->id)->first();

        // Customer 1 tries to view Customer 2's shipment via API
        $response = $this->actingAs($customer1, 'sanctum')
            ->getJson('/api/shipments/' . $shipmentCustomer2->id);

        $response->assertStatus(403);
    }

    /**
     * Test Customer can view their own shipment
     */
    public function test_customer_can_view_own_shipment_successfully()
    {
        $customer1 = User::where('email', 'customer.siti@example.com')->first();
        $ownShipment = Shipment::where('user_id', $customer1->id)->first();

        $response = $this->actingAs($customer1, 'sanctum')
            ->getJson('/api/shipments/' . $ownShipment->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.tracking_number', $ownShipment->tracking_number);
    }

    /**
     * Test Customer cannot perform staff status update
     */
    public function test_customer_forbidden_from_updating_shipment_status()
    {
        $customer = User::where('email', 'customer.siti@example.com')->first();
        $shipment = Shipment::where('user_id', $customer->id)->first();

        $response = $this->actingAs($customer, 'sanctum')
            ->patchJson('/api/shipments/' . $shipment->id, [
                'status' => 'delivered',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test Operator can access and update operational shipment
     */
    public function test_operator_can_view_operational_shipments()
    {
        $operator = User::where('email', 'operator.jkt@cargotrace.test')->first();

        $response = $this->actingAs($operator, 'sanctum')
            ->getJson('/api/shipments');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data']]);
    }
}

