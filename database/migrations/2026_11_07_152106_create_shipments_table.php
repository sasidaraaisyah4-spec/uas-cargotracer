<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->nullOnDelete();
            $table->foreignId('origin_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('locations')->nullOnDelete();

            // Sender Information
            $table->string('sender_name');
            $table->string('sender_phone', 30);
            $table->text('sender_address');

            // Recipient Information
            $table->string('recipient_name');
            $table->string('recipient_phone', 30);
            $table->text('recipient_address');

            // Cargo & Dimension Specifications (UAS requirement)
            $table->string('cargo_type', 100)->default('general_cargo');
            $table->enum('service_type', ['regular', 'express', 'same_day', 'cargo', 'economy'])->default('regular');
            $table->decimal('total_weight', 10, 2)->default(0.00)->comment('weight in kg');
            $table->decimal('total_volume', 10, 3)->default(0.000)->comment('volume in cbm');
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->text('notes')->nullable();

            // State Machine Status Lifecycle (UAS requirement)
            $table->enum('status', [
                'created',
                'pending',
                'booked',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'exception',
                'delivered',
                'cancelled'
            ])->default('created')->index();

            // Expected Schedule vs Estimated / Actual Timestamps (UAS ETA calculation)
            $table->timestamp('expected_departure_at')->nullable();
            $table->timestamp('expected_arrival_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Deterministic Delay Evaluation Fields (UAS requirement)
            $table->boolean('is_delayed')->default(false)->index();
            $table->decimal('delay_hours', 8, 2)->default(0.00);
            $table->enum('delay_severity', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE')->index();

            $table->timestamps();

            // Composite indexes for fast queries & analytics
            $table->index(['status', 'created_at']);
            $table->index(['is_delayed', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
