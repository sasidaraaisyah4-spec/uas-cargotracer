<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'item_name',
        'item_category',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'declared_value',
        'is_hazardous',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'is_hazardous' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Shipment to which this item belongs.
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
