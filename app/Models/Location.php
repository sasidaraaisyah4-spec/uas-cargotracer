<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'city',
        'state_province',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Shipments where this location is the origin.
     */
    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_location_id');
    }

    /**
     * Shipments where this location is the destination.
     */
    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_location_id');
    }

    /**
     * Tracking events recorded at this location.
     */
    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'location_id');
    }
}
