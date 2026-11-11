<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'manifest_created',
                'package_received',
                'in_transit',
                'arrived_at_facility',
                'departed_from_facility',
                'out_for_delivery',
                'delivery_attempted',
                'delivered',
                'exception',
                'returned'
            ])->index();

            $table->string('event_title');
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking_events');
    }
}
