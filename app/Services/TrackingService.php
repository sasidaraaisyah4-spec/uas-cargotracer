<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    protected EtaService $etaService;

    public function __construct(EtaService $etaService)
    {
        $this->etaService = $etaService;
    }

    /**
     * Add chronological tracking event strictly append-only (never overwrite old events)
     *
     * Rules:
     * - Must validate chronological sequence: new occurred_at >= last event's occurred_at
     * - Synchronize latest status & coordinate to shipment
     * - Trigger ETA evaluation if estimated_delivery_at provided
     * - Send notification & log audit
     */
    public function addTrackingEvent(Shipment $shipment, User $actor, array $data): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $actor, $data) {
            $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now();

            // Validate chronological sequence
            $lastEvent = $shipment->trackingEvents()->latest('occurred_at')->first();
            if ($lastEvent && $occurredAt->lessThan($lastEvent->occurred_at)) {
                $lastFormatted = $lastEvent->occurred_at->toDateTimeString();
                $newFormatted = $occurredAt->toDateTimeString();
                throw new InvalidArgumentException("Chronological error: New tracking event time ({$newFormatted}) cannot be earlier than previous event time ({$lastFormatted}).");
            }

            // Create tracking event (append-only)
            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $data['location_id'] ?? null,
                'created_by' => $actor->id,
                'status' => $data['status'] ?? $shipment->status,
                'event_title' => $data['event_title'],
                'description' => $data['description'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'occurred_at' => $occurredAt,
            ]);

            // Update shipment status if provided and different
            $shipmentUpdates = [];
            if (!empty($data['status']) && $data['status'] !== $shipment->status) {
                // Check if terminal state reached
                if ($data['status'] === 'delivered') {
                    $shipmentUpdates['delivered_at'] = $occurredAt;
                }
                $shipmentUpdates['status'] = $data['status'];
            }

            // If updated ETA given in event
            if (!empty($data['estimated_delivery_at'])) {
                $shipmentUpdates['estimated_delivery_at'] = Carbon::parse($data['estimated_delivery_at']);
            }

            if (!empty($shipmentUpdates)) {
                $shipment->update($shipmentUpdates);
                // Re-evaluate delay
                $this->etaService->applyEvaluation($shipment);
            }

            // Notify customer
            if ($shipment->user_id) {
                Notification::create([
                    'user_id' => $shipment->user_id,
                    'shipment_id' => $shipment->id,
                    'type' => 'info',
                    'title' => "Tracking Update: {$shipment->tracking_number}",
                    'message' => "Update perjalanan: {$event->event_title} ({$event->description})",
                    'is_read' => false,
                ]);
            }

            // Audit Log
            AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'tracking_event.added',
                'auditable_type' => TrackingEvent::class,
                'auditable_id' => $event->id,
                'old_values' => null,
                'new_values' => [
                    'shipment_id' => $shipment->id,
                    'title' => $event->event_title,
                    'status' => $event->status,
                    'occurred_at' => $occurredAt->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $event;
        });
    }
}

