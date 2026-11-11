<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('tracking_event_id')->nullable()->constrained('tracking_events')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('exception_type', [
                'delay',
                'weather_disruption',
                'damaged',
                'lost',
                'failed_attempt',
                'incorrect_address',
                'customs_hold',
                'other'
            ])->index();

            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
            $table->text('reason');
            $table->text('resolution_notes')->nullable();

            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_exceptions');
    }
}
