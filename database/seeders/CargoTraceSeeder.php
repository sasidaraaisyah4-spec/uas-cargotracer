<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
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
        // 1. Users (Admin, Operator, Driver, Customer)
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.test'],
            [
                'name' => 'Admin CargoTrace',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081200000001',
                'is_active' => true,
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator.jkt@cargotrace.test'],
            [
                'name' => 'Operator Jakarta Hub',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'phone' => '081200000002',
                'is_active' => true,
            ]
        );

        $driver = User::firstOrCreate(
            ['email' => 'driver.budi@cargotrace.test'],
            [
                'name' => 'Budi Santoso (Driver)',
                'password' => Hash::make('password123'),
                'role' => 'driver',
                'phone' => '081200000003',
                'is_active' => true,
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer.siti@example.com'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081200000004',
                'is_active' => true,
            ]
        );

        // 2. Carriers
        $jne = Carrier::firstOrCreate(
            ['code' => 'JNE'],
            [
                'name' => 'JNE Express',
                'contact_person' => 'Customer Service JNE',
                'phone' => '021-29278888',
                'email' => 'customercare@jne.co.id',
                'status' => 'active',
            ]
        );

        $sicepat = Carrier::firstOrCreate(
            ['code' => 'SICEPAT'],
            [
                'name' => 'SiCepat Ekspres',
                'contact_person' => 'Partner Care SiCepat',
                'phone' => '021-50200050',
                'email' => 'contact@sicepat.com',
                'status' => 'active',
            ]
        );

        // 3. Locations
        $hubJkt = Location::firstOrCreate(
            ['code' => 'HUB-JKT-01'],
            [
                'name' => 'Main Sorting Hub Jakarta',
                'type' => 'hub',
                'address' => 'Jl. Daan Mogot KM 12 No. 8',
                'city' => 'Jakarta Barat',
                'state_province' => 'DKI Jakarta',
                'postal_code' => '11740',
                'country_code' => 'ID',
                'latitude' => -6.1553000,
                'longitude' => 106.7431000,
            ]
        );

        $hubSby = Location::firstOrCreate(
            ['code' => 'HUB-SBY-01'],
            [
                'name' => 'Central Hub Surabaya',
                'type' => 'hub',
                'address' => 'Jl. Rungkut Industri Raya No. 45',
                'city' => 'Surabaya',
                'state_province' => 'Jawa Timur',
                'postal_code' => '60293',
                'country_code' => 'ID',
                'latitude' => -7.3242000,
                'longitude' => 112.7538000,
            ]
        );

        // 4. Sample Shipment (In Transit)
        $shipment = Shipment::firstOrCreate(
            ['tracking_number' => 'CT-20261107-JKTSBY001'],
            [
                'user_id' => $customer->id,
                'carrier_id' => $jne->id,
                'origin_location_id' => $hubJkt->id,
                'destination_location_id' => $hubSby->id,
                'sender_name' => 'PT Sumber Elektronik Mandiri',
                'sender_phone' => '081399887766',
                'sender_address' => 'Ruko Mangga Dua Mall Blok B No. 14, Jakarta Pusat',
                'recipient_name' => 'CV Maju Lancar Surabaya',
                'recipient_phone' => '081822334455',
                'recipient_address' => 'Jl. Basuki Rahmat No. 88, Tegalsari, Surabaya',
                'status' => 'in_transit',
                'service_type' => 'express',
                'total_weight' => 4.50,
                'total_price' => 125000.00,
                'payment_status' => 'paid',
                'notes' => 'Fragile items: handle with care',
                'estimated_delivery_at' => now()->addDays(2),
            ]
        );

        // 5. Items inside shipment
        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment->id, 'item_name' => 'Mechanical Keyboard RGB'],
            [
                'item_category' => 'Electronics',
                'quantity' => 2,
                'weight' => 2.00,
                'length' => 45.00,
                'width' => 15.00,
                'height' => 5.00,
                'declared_value' => 1500000.00,
                'is_hazardous' => false,
                'description' => '2 units mechanical keyboard with blue switches',
            ]
        );

        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment->id, 'item_name' => 'Studio Monitor Headphone'],
            [
                'item_category' => 'Electronics',
                'quantity' => 1,
                'weight' => 0.50,
                'length' => 25.00,
                'width' => 20.00,
                'height' => 10.00,
                'declared_value' => 850000.00,
                'is_hazardous' => false,
                'description' => 'Closed-back audio monitor headphone',
            ]
        );

        // 6. Tracking Timeline Events
        $event1 = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $hubJkt->id,
            'created_by' => $operator->id,
            'status' => 'manifest_created',
            'event_title' => 'Manifest Pengiriman Dibuat',
            'description' => 'Paket telah didaftarkan ke sistem CargoTrace oleh pengirim.',
            'occurred_at' => now()->subHours(10),
        ]);

        $event2 = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $hubJkt->id,
            'created_by' => $operator->id,
            'status' => 'package_received',
            'event_title' => 'Paket Diterima di Hub Jakarta',
            'description' => 'Paket telah diterima di fasilitas Main Sorting Hub Jakarta dan telah ditimbang.',
            'occurred_at' => now()->subHours(6),
        ]);

        $event3 = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $hubJkt->id,
            'created_by' => $driver->id,
            'status' => 'in_transit',
            'event_title' => 'Paket Dalam Perjalanan Menuju Surabaya',
            'description' => 'Paket diberangkatkan dengan armada darat menuju Central Hub Surabaya.',
            'occurred_at' => now()->subHours(2),
        ]);

        // 7. Audit Log
        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'shipment.created',
            'auditable_type' => Shipment::class,
            'auditable_id' => $shipment->id,
            'old_values' => null,
            'new_values' => [
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'total_price' => $shipment->total_price,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder/Console',
        ]);
    }
}
