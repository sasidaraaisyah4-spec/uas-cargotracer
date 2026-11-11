<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CargoTraceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // -----------------------------------------------------------------
        // 1. USERS & ROLES
        // -----------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.test'],
            [
                'name' => 'Alexander Pierce (Admin)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+6281200000001',
                'is_active' => true,
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator.jkt@cargotrace.test'],
            [
                'name' => 'Bambang Sudiro (Logistics Operator)',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'phone' => '+6281200000002',
                'is_active' => true,
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer.siti@example.com'],
            [
                'name' => 'Siti Nurhaliza (Customer)',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6281200000003',
                'is_active' => true,
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'customer.john@globaltrade.test'],
            [
                'name' => 'Johnathan Davis (Global Importer)',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6281200000004',
                'is_active' => true,
            ]
        );

        // -----------------------------------------------------------------
        // 2. CARRIERS (Air, Sea & Land)
        // -----------------------------------------------------------------
        $maersk = Carrier::firstOrCreate(
            ['code' => 'MAEU'],
            [
                'name' => 'Maersk Line Shipping',
                'contact_person' => 'Capt. Henrik Lind',
                'phone' => '+622152998800',
                'email' => 'support.id@maersk.com',
                'status' => 'active',
            ]
        );

        $dhl = Carrier::firstOrCreate(
            ['code' => 'DHLX'],
            [
                'name' => 'DHL Global Forwarding',
                'contact_person' => 'Markus Meyer',
                'phone' => '+622179178888',
                'email' => 'support@dhl.com',
                'status' => 'active',
            ]
        );

        $samudera = Carrier::firstOrCreate(
            ['code' => 'SMDR'],
            [
                'name' => 'Samudera Indonesia Logistics',
                'contact_person' => 'Rian Hidayat',
                'phone' => '+62215480088',
                'email' => 'ops@samudera.id',
                'status' => 'active',
            ]
        );

        // -----------------------------------------------------------------
        // 3. LOCATIONS (With Realistic Port / Airport Coordinates)
        // -----------------------------------------------------------------
        $locJktPort = Location::firstOrCreate(
            ['code' => 'IDJKT-TPP'],
            [
                'name' => 'Port of Tanjung Priok (Jakarta)',
                'type' => 'port',
                'address' => 'Jl. Raya Pelabuhan No. 9, Tanjung Priok',
                'city' => 'Jakarta Utara',
                'state_province' => 'DKI Jakarta',
                'postal_code' => '14310',
                'country_code' => 'ID',
                'latitude' => -6.1022000,
                'longitude' => 106.8825000,
            ]
        );

        $locSbyPort = Location::firstOrCreate(
            ['code' => 'IDSUB-TPR'],
            [
                'name' => 'Port of Tanjung Perak (Surabaya)',
                'type' => 'port',
                'address' => 'Jl. Perak Timur No. 610',
                'city' => 'Surabaya',
                'state_province' => 'Jawa Timur',
                'postal_code' => '60165',
                'country_code' => 'ID',
                'latitude' => -7.1985000,
                'longitude' => 112.7335000,
            ]
        );

        $locSinPort = Location::firstOrCreate(
            ['code' => 'SGSIN-PSA'],
            [
                'name' => 'PSA Singapore Jurong Terminal',
                'type' => 'port',
                'address' => '7B Keppel Rd, Tanjong Pagar Terminal',
                'city' => 'Singapore',
                'state_province' => 'Central Region',
                'postal_code' => '089055',
                'country_code' => 'SG',
                'latitude' => 1.2644000,
                'longitude' => 103.8222000,
            ]
        );

        $locHubSby = Location::firstOrCreate(
            ['code' => 'HUB-SUB-01'],
            [
                'name' => 'Surabaya Cargo Distribution Center',
                'type' => 'warehouse',
                'address' => 'Kawasan Industri Rungkut Blok H-12',
                'city' => 'Surabaya',
                'state_province' => 'Jawa Timur',
                'postal_code' => '60293',
                'country_code' => 'ID',
                'latitude' => -7.3242000,
                'longitude' => 112.7538000,
            ]
        );

        // -----------------------------------------------------------------
        // 4. SHIPMENT 1: IN_TRANSIT (On-Time Maritime Cargo)
        // -----------------------------------------------------------------
        $shipment1 = Shipment::firstOrCreate(
            ['tracking_number' => 'CT-20261107-JKTSBY001'],
            [
                'user_id' => $customer->id,
                'carrier_id' => $samudera->id,
                'origin_location_id' => $locJktPort->id,
                'destination_location_id' => $locSbyPort->id,
                'sender_name' => 'PT Sumber Elektronik Mandiri',
                'sender_phone' => '+6281399887766',
                'sender_address' => 'Kawasan Berikat Nusantara, Tanjung Priok, Jakarta',
                'recipient_name' => 'CV Maju Lancar Surabaya',
                'recipient_phone' => '+6281822334455',
                'recipient_address' => 'Jl. Basuki Rahmat No. 88, Tegalsari, Surabaya',
                'cargo_type' => 'Electronics & Peripherals',
                'service_type' => 'cargo',
                'total_weight' => 1250.00,
                'total_volume' => 3.450,
                'total_price' => 8750000.00,
                'payment_status' => 'paid',
                'notes' => 'Container sealed #SMDR-449102. Handle high-value audio electronics carefully.',
                'status' => 'in_transit',
                'expected_departure_at' => now()->subHours(20),
                'expected_arrival_at' => now()->addHours(24),
                'estimated_delivery_at' => now()->addHours(26), // +2 hours (On-time because < 12h threshold)
                'delivered_at' => null,
                'is_delayed' => false,
                'delay_hours' => 2.00,
                'delay_severity' => 'LOW',
            ]
        );

        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'item_name' => 'Industrial Mechanical Keyboards'],
            [
                'item_category' => 'Electronics',
                'quantity' => 250,
                'weight' => 500.00,
                'length' => 45.00,
                'width' => 15.00,
                'height' => 5.00,
                'declared_value' => 75000000.00,
                'is_hazardous' => false,
                'description' => 'Blue Switch Mechanical Keyboards packed in pallets',
            ]
        );

        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'item_name' => 'Studio Reference Monitors'],
            [
                'item_category' => 'Audio Equipment',
                'quantity' => 100,
                'weight' => 750.00,
                'length' => 30.00,
                'width' => 25.00,
                'height' => 40.00,
                'declared_value' => 120000000.00,
                'is_hazardous' => false,
                'description' => 'Active 8-inch Studio Monitor Pairs',
            ]
        );

        // Tracking Events for Shipment 1
        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $locJktPort->id,
            'created_by' => $operator->id,
            'status' => 'created',
            'event_title' => 'Shipment Manifest Created',
            'description' => 'Electronic cargo booking created and documentation verified.',
            'latitude' => -6.1022000,
            'longitude' => 106.8825000,
            'occurred_at' => now()->subHours(24),
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $locJktPort->id,
            'created_by' => $operator->id,
            'status' => 'picked_up',
            'event_title' => 'Container Loaded at Tanjung Priok',
            'description' => 'Cargo inspected, customs clearance stamped, loaded into vessel SMDR-Nusantara.',
            'latitude' => -6.1022000,
            'longitude' => 106.8825000,
            'occurred_at' => now()->subHours(18),
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => null,
            'created_by' => $operator->id,
            'status' => 'in_transit',
            'event_title' => 'Vessel Sailing Java Sea Corridor',
            'description' => 'Vessel en route off Indramayu maritime corridor. ETA Surabaya updated.',
            'latitude' => -6.1820000,
            'longitude' => 108.3200000,
            'occurred_at' => now()->subHours(4),
        ]);

        // -----------------------------------------------------------------
        // 5. SHIPMENT 2: DELAYED with ACTIVE EXCEPTION (Customs / Weather)
        // -----------------------------------------------------------------
        $shipment2 = Shipment::firstOrCreate(
            ['tracking_number' => 'CT-20261108-SGSUB999'],
            [
                'user_id' => $customer2->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $locSinPort->id,
                'destination_location_id' => $locSbyPort->id,
                'sender_name' => 'SingaTech Maritime Supply Pte Ltd',
                'sender_phone' => '+6567891234',
                'sender_address' => '7B Keppel Road, Tanjong Pagar Logistics Park, Singapore',
                'recipient_name' => 'PT Nusantara Heavy Machineries',
                'recipient_phone' => '+628113456789',
                'recipient_address' => 'Kawasan Industri Gresik Kavling 10-14, Gresik, Jawa Timur',
                'cargo_type' => 'Industrial Hydraulic Valves',
                'service_type' => 'cargo',
                'total_weight' => 4800.00,
                'total_volume' => 8.200,
                'total_price' => 34500000.00,
                'payment_status' => 'paid',
                'notes' => 'Heavy lift cargo. Requires crane offloader at Tanjung Perak destination.',
                'status' => 'in_transit',
                'expected_departure_at' => now()->subHours(72),
                'expected_arrival_at' => now()->subHours(12), // Expected arrival was 12h ago!
                'estimated_delivery_at' => now()->addHours(20), // ETA now: 32 hours total delay!
                'delivered_at' => null,
                'is_delayed' => true,
                'delay_hours' => 32.00,
                'delay_severity' => 'HIGH',
            ]
        );

        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment2->id, 'item_name' => 'Hydraulic Actuators & Control Valves'],
            [
                'item_category' => 'Heavy Machinery',
                'quantity' => 12,
                'weight' => 4800.00,
                'length' => 120.00,
                'width' => 80.00,
                'height' => 110.00,
                'declared_value' => 450000000.00,
                'is_hazardous' => false,
                'description' => 'Steel-cased hydraulic pressure controllers',
            ]
        );

        $teSin = TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'location_id' => $locSinPort->id,
            'created_by' => $operator->id,
            'status' => 'created',
            'event_title' => 'Export Manifest Lodged in Singapore',
            'description' => 'Cargo received at Jurong PSA Terminal and verified by freight forwarder.',
            'latitude' => 1.2644000,
            'longitude' => 103.8222000,
            'occurred_at' => now()->subHours(68),
        ]);

        $teBatam = TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'location_id' => null,
            'created_by' => $operator->id,
            'status' => 'in_transit',
            'event_title' => 'Vessel Anchored near Karimun Strait',
            'description' => 'Heavy tropical squall and wave height exceeded safety threshold. Vessel temporarily anchored.',
            'latitude' => 1.0900000,
            'longitude' => 103.4500000,
            'occurred_at' => now()->subHours(24),
        ]);

        // Active Exception for Shipment 2
        ShipmentException::create([
            'shipment_id' => $shipment2->id,
            'tracking_event_id' => $teBatam->id,
            'reported_by' => $operator->id,
            'resolved_by' => null,
            'exception_type' => 'weather_delay',
            'severity' => 'high',
            'reason' => 'Extreme weather conditions in South China Sea / Karimun Strait caused navigation suspension by harbor master.',
            'resolution_notes' => 'Monitoring maritime radar. Vessel projected to resume journey once squall subsides.',
            'status' => 'investigating',
            'detected_at' => now()->subHours(24),
            'resolved_at' => null,
        ]);

        // -----------------------------------------------------------------
        // 6. SHIPMENT 3: DELIVERED (Terminal State Demo)
        // -----------------------------------------------------------------
        $shipment3 = Shipment::firstOrCreate(
            ['tracking_number' => 'CT-20261101-JKTSUB888'],
            [
                'user_id' => $customer->id,
                'carrier_id' => $dhl->id,
                'origin_location_id' => $locJktPort->id,
                'destination_location_id' => $locHubSby->id,
                'sender_name' => 'PT Tekno Logika Mandiri',
                'sender_phone' => '+62811889900',
                'sender_address' => 'Gedung Cyber 2 Lantai 15, Kuningan, Jakarta Selatan',
                'recipient_name' => 'Siti Nurhaliza (Customer)',
                'recipient_phone' => '+6281200000003',
                'recipient_address' => 'Perumahan Darmo Permai Indah Blok C No. 21, Sukomanunggal, Surabaya',
                'cargo_type' => 'Pharmaceutical Vaccines',
                'service_type' => 'express',
                'total_weight' => 35.00,
                'total_volume' => 0.120,
                'total_price' => 1850000.00,
                'payment_status' => 'paid',
                'notes' => 'Temperature-controlled dry ice container (2-8°C)',
                'status' => 'delivered',
                'expected_departure_at' => now()->subDays(5),
                'expected_arrival_at' => now()->subDays(3),
                'estimated_delivery_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(3)->addHours(2),
                'is_delayed' => false,
                'delay_hours' => 0.00,
                'delay_severity' => 'NONE',
            ]
        );

        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment3->id, 'item_name' => 'Temperature Controlled Medicine Pack'],
            [
                'item_category' => 'Medical Supply',
                'quantity' => 10,
                'weight' => 35.00,
                'length' => 40.00,
                'width' => 40.00,
                'height' => 30.00,
                'declared_value' => 50000000.00,
                'is_hazardous' => false,
                'description' => 'Medical insulated packaging with active data logger',
            ]
        );

        TrackingEvent::create([
            'shipment_id' => $shipment3->id,
            'location_id' => $locHubSby->id,
            'created_by' => $operator->id,
            'status' => 'delivered',
            'event_title' => 'Shipment Successfully Delivered',
            'description' => 'Package handed directly to recipient Siti Nurhaliza with valid digital signature.',
            'latitude' => -7.2840000,
            'longitude' => 112.6950000,
            'occurred_at' => now()->subDays(3)->addHours(2),
        ]);

        // -----------------------------------------------------------------
        // 7. NOTIFICATIONS & AUDIT LOGS
        // -----------------------------------------------------------------
        Notification::create([
            'user_id' => $customer2->id,
            'shipment_id' => $shipment2->id,
            'type' => 'delay',
            'title' => 'Keterlambatan Pengiriman: ' . $shipment2->tracking_number,
            'message' => 'Kapal pengangkut kargo Anda mengalami penundaan karena cuaca buruk di Selat Karimun (Delay 32 Jam).',
            'is_read' => false,
            'created_at' => now()->subHours(20),
        ]);

        Notification::create([
            'user_id' => $customer->id,
            'shipment_id' => $shipment1->id,
            'type' => 'info',
            'title' => 'Update Lokasi: ' . $shipment1->tracking_number,
            'message' => 'Kapal SMDR-Nusantara sedang berlayar melintasi koridor laut Jawa.',
            'is_read' => true,
            'read_at' => now()->subHours(3),
            'created_at' => now()->subHours(4),
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'shipment.status_updated',
            'auditable_type' => Shipment::class,
            'auditable_id' => $shipment2->id,
            'old_values' => ['status' => 'picked_up', 'is_delayed' => false],
            'new_values' => ['status' => 'in_transit', 'is_delayed' => true, 'delay_hours' => 32.0],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CargoTrace/OperatorConsole',
        ]);
    }
}
