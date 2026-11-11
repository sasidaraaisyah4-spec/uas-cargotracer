<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\ExternalApiService;
use Illuminate\Http\Request;

class TrackingWebController extends Controller
{
    protected ExternalApiService $externalApiService;

    public function __construct(ExternalApiService $externalApiService)
    {
        $this->externalApiService = $externalApiService;
    }

    public function index(Request $request)
    {
        $trackingNumber = $request->query('tracking_number');
        $shipment = null;
        $searched = false;
        $mapData = null;
        $weatherData = null;

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
                'exceptions' => function ($q) {
                    $q->with('reporter', 'resolver');
                }
            ])->where('tracking_number', trim($trackingNumber))->first();

            if ($shipment) {
                // Generate structured map dataset (Line: Origin -> Checkpoints -> Destination)
                $mapData = $this->externalApiService->getMapData($shipment);

                // Fetch real-time weather at current position
                $currLat = $mapData['current_position']['latitude'];
                $currLng = $mapData['current_position']['longitude'];
                $weatherData = $this->externalApiService->getCurrentWeather($currLat, $currLng);
            }
        }

        return view('welcome', compact('shipment', 'trackingNumber', 'searched', 'mapData', 'weatherData'));
    }
}
