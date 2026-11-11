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

            // Status and Service Details
            $table->enum('status', [
                'pending',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'returned',
                'cancelled'
            ])->default('pending')->index();

            $table->enum('service_type', ['regular', 'express', 'same_day', 'cargo', 'economy'])->default('regular');
            $table->decimal('total_weight', 10, 2)->default(0.00)->comment('in kg');
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->text('notes')->nullable();

            // Timeline timestamps
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['status', 'created_at']);
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
