<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_category', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 10, 2)->default(0.00)->comment('weight per item in kg');
            $table->decimal('length', 8, 2)->nullable()->comment('in cm');
            $table->decimal('width', 8, 2)->nullable()->comment('in cm');
            $table->decimal('height', 8, 2)->nullable()->comment('in cm');
            $table->decimal('declared_value', 15, 2)->nullable();
            $table->boolean('is_hazardous')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_items');
    }
}
