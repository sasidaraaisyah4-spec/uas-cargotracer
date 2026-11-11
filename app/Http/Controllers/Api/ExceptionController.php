<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportExceptionRequest;
use App\Http\Requests\UpdateExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionController extends Controller
{
    protected ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    /**
     * GET /api/shipments/{id}/exceptions
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

        $exceptions = $shipment->exceptions()
            ->with(['reporter:id,name', 'resolver:id,name', 'trackingEvent'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $exceptions,
        ]);
    }

    /**
     * POST /api/shipments/{id}/exceptions
     * Report an operational exception
     */
    public function store(ReportExceptionRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['operator', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action. Only staff or administrator can report exceptions.',
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
            $exception = $this->exceptionService->reportException($shipment, $user, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Exception reported successfully. Shipment status flagged to EXCEPTION.',
                'data' => $exception->load(['reporter:id,name', 'shipment']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PATCH /api/exceptions/{id}
     * Update exception status (OPEN -> INVESTIGATING -> RESOLVED)
     */
    public function update(UpdateExceptionRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['operator', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action. Only staff or administrator can update exceptions.',
            ], 403);
        }

        $exception = ShipmentException::with('shipment')->find($id);
        if (!$exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception not found',
            ], 404);
        }

        try {
            $validated = $request->validated();
            $updated = $this->exceptionService->updateExceptionStatus(
                $exception,
                $user,
                $validated['status'],
                $validated['resolution_notes'] ?? null,
                $validated['resume_shipment_status'] ?? 'in_transit'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Exception status updated successfully',
                'data' => $updated->fresh(['reporter:id,name', 'resolver:id,name', 'shipment']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

