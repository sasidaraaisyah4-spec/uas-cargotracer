<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\EtaService;
use App\Services\ShipmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;
    protected EtaService $etaService;

    public function __construct(ShipmentService $shipmentService, EtaService $etaService)
    {
        $this->shipmentService = $shipmentService;
        $this->etaService = $etaService;
    }

    /**
     * GET /api/shipments
     * List shipments with server-side pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $shipments = $this->shipmentService->getPaginatedShipments($request->user(), $request->all(), $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $shipments,
        ]);
    }

    /**
     * POST /api/shipments
     * Create shipment (Operator, Admin, or Customer)
     */
    public function store(CreateShipmentRequest $request): JsonResponse
    {
        try {
            $shipment = $this->shipmentService->createShipment($request->user(), $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment created successfully',
                'data' => $shipment->load(['items', 'trackingEvents', 'carrier', 'originLocation', 'destinationLocation']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/shipments/{id}
     * Get single shipment details with authorization check
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $shipment = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents' => function ($q) {
                $q->with('location', 'creator:id,name,role')->orderBy('occurred_at', 'asc');
            },
            'exceptions' => function ($q) {
                $q->with('reporter:id,name', 'resolver:id,name');
            },
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        // Authorization rule: Customer can only view their own shipment
        if ($user->role === 'customer' && $shipment->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have permission to view this shipment.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ]);
    }

    /**
     * PUT/PATCH /api/shipments/{id}
     * Update shipment status and parameters
     */
    public function update(UpdateShipmentRequest $request, $id): JsonResponse
    {
        $user = $request->user();
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        // Only Operator and Admin can update shipment state
        if (!in_array($user->role, ['operator', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action. Only staff or administrator can update shipments.',
            ], 403);
        }

        try {
            $validated = $request->validated();

            if (!empty($validated['status']) && $validated['status'] !== $shipment->status) {
                $shipment = $this->shipmentService->transitionStatus($shipment, $validated['status'], $user, $validated['notes'] ?? null);
            }

            if (!empty($validated['carrier_id'])) {
                $shipment->update(['carrier_id' => $validated['carrier_id']]);
            }

            if (!empty($validated['estimated_delivery_at'])) {
                $shipment->update(['estimated_delivery_at' => $validated['estimated_delivery_at']]);
                $this->etaService->applyEvaluation($shipment);
            }

            if (!empty($validated['payment_status'])) {
                $shipment->update(['payment_status' => $validated['payment_status']]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment updated successfully',
                'data' => $shipment->fresh(['carrier', 'items', 'trackingEvents']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /api/shipments/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action. Only administrator can delete shipments.',
            ], 403);
        }

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        $shipment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment deleted successfully',
        ]);
    }
}
