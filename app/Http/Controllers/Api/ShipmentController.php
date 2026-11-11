<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    /**
     * List shipments with filters and pagination
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Shipment::with([
            'carrier:id,name,code',
            'originLocation:id,name,city',
            'destinationLocation:id,name,city',
        ]);

        // If customer, only see own shipments
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $shipments,
        ]);
    }

    /**
     * Create a new shipment with items & initial tracking event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'carrier_id' => 'nullable|exists:carriers,id',
            'origin_location_id' => 'nullable|exists:locations,id',
            'destination_location_id' => 'nullable|exists:locations,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:30',
            'sender_address' => 'required|string',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_address' => 'required|string',
            'service_type' => 'required|in:regular,express,same_day,cargo,economy',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'estimated_delivery_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_category' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'required|numeric|min:0',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.width' => 'nullable|numeric|min:0',
            'items.*.height' => 'nullable|numeric|min:0',
            'items.*.declared_value' => 'nullable|numeric|min:0',
            'items.*.is_hazardous' => 'nullable|boolean',
            'items.*.description' => 'nullable|string',
        ]);

        $user = $request->user();

        $shipment = DB::transaction(function () use ($validated, $user, $request) {
            // Calculate total weight
            $totalWeight = collect($validated['items'])->sum(function ($item) {
                return $item['weight'] * $item['quantity'];
            });

            // Generate unique tracking number (e.g. CT-20261107-ABC1234)
            $trackingNumber = 'CT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(7));

            $shipment = Shipment::create([
                'tracking_number' => $trackingNumber,
                'user_id' => $user->id,
                'carrier_id' => $validated['carrier_id'] ?? null,
                'origin_location_id' => $validated['origin_location_id'] ?? null,
                'destination_location_id' => $validated['destination_location_id'] ?? null,
                'sender_name' => $validated['sender_name'],
                'sender_phone' => $validated['sender_phone'],
                'sender_address' => $validated['sender_address'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'recipient_address' => $validated['recipient_address'],
                'status' => 'pending',
                'service_type' => $validated['service_type'],
                'total_weight' => $totalWeight,
                'total_price' => $validated['total_price'] ?? 0.00,
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'] ?? null,
                'estimated_delivery_at' => $validated['estimated_delivery_at'] ?? null,
            ]);

            // Save items
            foreach ($validated['items'] as $itemData) {
                $shipment->items()->create($itemData);
            }

            // Create initial tracking event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $shipment->origin_location_id,
                'created_by' => $user->id,
                'status' => 'manifest_created',
                'event_title' => 'Manifest Pengiriman Dibuat',
                'description' => 'Paket telah didaftarkan dan menunggu penjemputan/drop-off.',
                'occurred_at' => now(),
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'shipment.created',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => null,
                'new_values' => $shipment->only(['tracking_number', 'status', 'total_weight', 'total_price']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $shipment;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment created successfully',
            'data' => $shipment->load(['items', 'trackingEvents']),
        ], 201);
    }

    /**
     * Show single shipment details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator:id,name,role',
            'exceptions',
        ]);

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        $shipment = $query->find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ]);
    }
}
