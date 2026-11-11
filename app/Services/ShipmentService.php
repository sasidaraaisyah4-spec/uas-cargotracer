<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ShipmentService
{
    protected EtaService $etaService;

    public function __construct(EtaService $etaService)
    {
        $this->etaService = $etaService;
    }

    /**
     * Query shipments with role filtering, search, multi-filters, and server-side pagination
     */
    public function getPaginatedShipments(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Shipment::with([
            'carrier:id,name,code',
            'originLocation:id,name,city,type',
            'destinationLocation:id,name,city,type',
            'latestTrackingEvent',
            'openExceptions',
        ]);

        // Customer can only view their own shipments
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        // Search by tracking number, sender, recipient
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Carrier filter
        if (!empty($filters['carrier_id'])) {
            $query->where('carrier_id', $filters['carrier_id']);
        }

        // Origin filter
        if (!empty($filters['origin_location_id'])) {
            $query->where('origin_location_id', $filters['origin_location_id']);
        }

        // Destination filter
        if (!empty($filters['destination_location_id'])) {
            $query->where('destination_location_id', $filters['destination_location_id']);
        }

        // Delay filter
        if (isset($filters['is_delayed']) && $filters['is_delayed'] !== '') {
            $query->where('is_delayed', (bool) $filters['is_delayed']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
    }

    /**
     * Create shipment with automatic tracking number, weight/volume calculation,
     * DB transaction, initial tracking event, and audit log.
     */
    public function createShipment(User $creator, array $data): Shipment
    {
        return DB::transaction(function () use ($creator, $data) {
            $trackingNumber = 'CT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(7));

            // Calculate total weight and total volume from items
            $items = $data['items'] ?? [];
            $totalWeight = 0.00;
            $totalVolume = 0.000;

            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $weight = (float) ($item['weight'] ?? 0.00);
                $totalWeight += ($weight * $qty);

                // Auto calculate volume in CBM (m3): (L x W x H in cm) / 1,000,000
                $l = (float) ($item['length'] ?? 0);
                $w = (float) ($item['width'] ?? 0);
                $h = (float) ($item['height'] ?? 0);

                if ($l > 0 && $w > 0 && $h > 0) {
                    $itemVolCbm = ($l * $w * $h) / 1000000;
                    $totalVolume += ($itemVolCbm * $qty);
                }
            }

            // Create shipment model
            $shipment = Shipment::create([
                'tracking_number' => $trackingNumber,
                'user_id' => $data['user_id'] ?? ($creator->role === 'customer' ? $creator->id : null),
                'carrier_id' => $data['carrier_id'] ?? null,
                'origin_location_id' => $data['origin_location_id'] ?? null,
                'destination_location_id' => $data['destination_location_id'] ?? null,
                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_address' => $data['sender_address'],
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_address' => $data['recipient_address'],
                'cargo_type' => $data['cargo_type'] ?? 'general_cargo',
                'service_type' => $data['service_type'] ?? 'regular',
                'total_weight' => round($totalWeight, 2),
                'total_volume' => round($totalVolume, 3),
                'total_price' => $data['total_price'] ?? 0.00,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'notes' => $data['notes'] ?? null,
                'status' => 'created',
                'expected_departure_at' => $data['expected_departure_at'] ?? now(),
                'expected_arrival_at' => $data['expected_arrival_at'] ?? now()->addDays(3),
                'estimated_delivery_at' => $data['estimated_delivery_at'] ?? now()->addDays(3),
            ]);

            // Save items
            foreach ($items as $itemData) {
                $shipment->items()->create($itemData);
            }

            // Initial delay check
            $this->etaService->applyEvaluation($shipment);

            // Create initial tracking event (append-only)
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $shipment->origin_location_id,
                'created_by' => $creator->id,
                'status' => 'created',
                'event_title' => 'Shipment Created',
                'description' => 'Shipment booking created in CargoTrace system.',
                'occurred_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'user_id' => $creator->id,
                'action' => 'shipment.created',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => null,
                'new_values' => $shipment->only(['tracking_number', 'status', 'total_weight', 'total_volume', 'total_price']),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $shipment;
        });
    }

    /**
     * Validate and transition shipment status using strict State Machine
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, User $actor, ?string $notes = null): Shipment
    {
        $currentStatus = $shipment->status;
        $allowedTransitions = config('cargotrace.allowed_transitions', []);

        // Disallow transitions from terminal states
        if (in_array($currentStatus, ['delivered', 'cancelled'])) {
            throw new InvalidArgumentException("Status '{$currentStatus}' is a terminal state and cannot be changed.");
        }

        // Validate allowed transitions
        $possibleNext = $allowedTransitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $possibleNext)) {
            $allowedStr = implode(', ', $possibleNext);
            throw new InvalidArgumentException("Illegal status transition from '{$currentStatus}' to '{$newStatus}'. Allowed transitions: [{$allowedStr}].");
        }

        return DB::transaction(function () use ($shipment, $newStatus, $actor, $notes, $currentStatus) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $shipment->update($updateData);

            // Append tracking event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'created_by' => $actor->id,
                'status' => $newStatus,
                'event_title' => 'Status Updated to ' . strtoupper(str_replace('_', ' ', $newStatus)),
                'description' => $notes ?: "Shipment status changed from {$currentStatus} to {$newStatus}.",
                'occurred_at' => now(),
            ]);

            // Create notification for customer if shipment is assigned to user
            if ($shipment->user_id) {
                Notification::create([
                    'user_id' => $shipment->user_id,
                    'shipment_id' => $shipment->id,
                    'type' => $newStatus === 'delivered' ? 'success' : 'info',
                    'title' => "Shipment {$shipment->tracking_number} Updated",
                    'message' => "Status pengiriman Anda telah diperbarui menjadi " . strtoupper(str_replace('_', ' ', $newStatus)),
                    'is_read' => false,
                ]);
            }

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'shipment.status_changed',
                'auditable_type' => Shipment::class,
                'auditable_id' => $shipment->id,
                'old_values' => ['status' => $currentStatus],
                'new_values' => ['status' => $newStatus, 'notes' => $notes],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $shipment;
        });
    }
}

