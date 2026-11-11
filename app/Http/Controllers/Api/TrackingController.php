<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * GET /api/track/{trackingNumber}
     * Public tracking by tracking number
     */
    public function track($trackingNumber): JsonResponse
    {
        $shipment = Shipment::with([
            'carrier:id,name,code',
            'originLocation:id,code,name,city,latitude,longitude',
            'destinationLocation:id,code,name,city,latitude,longitude',
            'items',
            'trackingEvents' => function ($query) {
                $query->with('location:id,code,name,city,latitude,longitude')->orderBy('occurred_at', 'asc');
            },
            'openExceptions',
        ])->where('tracking_number', trim($trackingNumber))->first();

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tracking number not found in CargoTrace system.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ]);
    }

    /**
     * GET /api/shipments/{id}/tracking
     * Get tracking history for a specific shipment
     */
    public function index(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        if ($user->role === 'customer' && $shipment->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.',
            ], 403);
        }

        $events = $shipment->trackingEvents()
            ->with(['location:id,code,name,city,latitude,longitude', 'creator:id,name,role'])
            ->orderBy('occurred_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'events' => $events,
            ],
        ]);
    }

    /**
     * POST /api/shipments/{id}/tracking
     * Add tracking event (Append-Only)
     */
    public function store(AddTrackingEventRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['operator', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action. Only staff or administrator can log tracking events.',
            ], 403);
        }

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        try {
            $event = $this->trackingService->addTrackingEvent($shipment, $user, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Tracking event logged successfully',
                'data' => $event->load(['location', 'creator:id,name,role']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
