<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingWebController extends Controller
{
    public function index(Request $request)
    {
        $trackingNumber = $request->query('tracking_number');
        $shipment = null;
        $searched = false;

        if ($trackingNumber) {
            $searched = true;
            $shipment = Shipment::with([
                'carrier',
                'originLocation',
                'destinationLocation',
                'items',
                'trackingEvents' => function ($q) {
                    $q->with('location', 'creator')->orderBy('occurred_at', 'desc');
                },
                'exceptions'
            ])->where('tracking_number', trim($trackingNumber))->first();
        }

        return view('welcome', compact('shipment', 'trackingNumber', 'searched'));
    }
}
