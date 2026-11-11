<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'tracking_event_id',
        'reported_by',
        'resolved_by',
        'exception_type',
        'severity',
        'reason',
        'resolution_notes',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Shipment affected by this exception.
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    /**
     * Tracking event where the exception occurred or was identified.
     */
    public function trackingEvent()
    {
        return $this->belongsTo(TrackingEvent::class, 'tracking_event_id');
    }

    /**
     * User/staff who reported the exception.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * User/staff who resolved the exception.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
