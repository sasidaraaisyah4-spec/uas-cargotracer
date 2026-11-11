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
        'cargo_type',
        'service_type',
        'total_weight',
        'total_volume',
        'total_price',
        'payment_status',
        'notes',
        'status',
        'expected_departure_at',
        'expected_arrival_at',
        'estimated_delivery_at',
        'delivered_at',
        'is_delayed',
        'delay_hours',
        'delay_severity',
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'total_volume' => 'decimal:3',
        'total_price' => 'decimal:2',
        'delay_hours' => 'decimal:2',
        'is_delayed' => 'boolean',
        'expected_departure_at' => 'datetime',
        'expected_arrival_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter shipments visible to a specific user based on role
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->role === 'customer') {
            return $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * User who owns the shipment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Assigned carrier
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    /**
     * Origin location
     */
    public function originLocation()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    /**
     * Destination location
     */
    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * Items included in this shipment
     */
    public function items()
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

    /**
     * Chronological tracking events
     */
    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_id')->orderBy('occurred_at', 'asc');
    }

    /**
     * Latest tracking event
     */
    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class, 'shipment_id')->latestOfMany('occurred_at');
    }

    /**
     * Exceptions reported on this shipment
     */
    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id');
    }

    /**
     * Active/open exceptions
     */
    public function openExceptions()
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id')->where('status', '!=', 'resolved');
    }

    /**
     * Notifications linked to this shipment
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'shipment_id');
    }

    /**
     * Polymorphic audit logs
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
