<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Public tracking by tracking number
     */
    public function track($trackingNumber)
    {
        $shipment = Shipment::with([
            'carrier:id,name,code',
            'originLocation:id,code,name,city',
            'destinationLocation:id,code,name,city',
            'items',
            'trackingEvents' => function ($query) {
                $query->with('location:id,code,name,city')->orderBy('occurred_at', 'asc');
            },
            'exceptions' => function ($query) {
                $query->select('id', 'shipment_id', 'exception_type', 'severity', 'reason', 'status', 'created_at');
            }
        ])->where('tracking_number', $trackingNumber)->first();

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tracking number not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ]);
    }
}
