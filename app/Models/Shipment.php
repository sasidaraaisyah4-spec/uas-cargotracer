<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'user_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'status',
        'service_type',
        'total_weight',
        'total_price',
        'payment_status',
        'notes',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'total_price' => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who created or owns the shipment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Assigned carrier.
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    /**
     * Origin location.
     */
    public function originLocation()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    /**
     * Destination location.
     */
    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * Items included in this shipment.
     */
    public function items()
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

    /**
     * Tracking timeline events for this shipment.
     */
    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_id')->orderBy('occurred_at', 'asc');
    }

    /**
     * Exceptions reported for this shipment.
     */
    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id');
    }

    /**
     * Polymorphic audit logs for this shipment.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
