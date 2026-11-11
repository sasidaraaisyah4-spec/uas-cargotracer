<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    /**
     * Report a new shipment exception.
     * Sets shipment status to special 'exception' status and flags is_delayed if critical.
     */
    public function reportException(Shipment $shipment, User $reporter, array $data): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $reporter, $data) {
            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'tracking_event_id' => $data['tracking_event_id'] ?? null,
                'reported_by' => $reporter->id,
                'exception_type' => $data['exception_type'],
                'severity' => $data['severity'] ?? 'medium',
                'reason' => $data['reason'],
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'status' => 'open',
                'detected_at' => now(),
            ]);

            // Set shipment to special 'exception' status as requested
            $oldStatus = $shipment->status;
            $shipment->update([
                'status' => 'exception',
                'is_delayed' => in_array($exception->severity, ['high', 'critical']) ? true : $shipment->is_delayed,
            ]);

            // Add corresponding tracking event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'created_by' => $reporter->id,
                'status' => 'exception',
                'event_title' => 'Operational Exception: ' . strtoupper(str_replace('_', ' ', $exception->exception_type)),
                'description' => $exception->reason,
                'occurred_at' => now(),
            ]);

            // Notify customer
            if ($shipment->user_id) {
                Notification::create([
                    'user_id' => $shipment->user_id,
                    'shipment_id' => $shipment->id,
                    'type' => 'exception',
                    'title' => "Kendala Pengiriman ({$shipment->tracking_number})",
                    'message' => "Terjadi kendala '{$exception->exception_type}': {$exception->reason}. Tim kami sedang menangani masalah ini.",
                    'is_read' => false,
                ]);
            }

            // Audit log
            AuditLog::create([
                'user_id' => $reporter->id,
                'action' => 'shipment.exception_reported',
                'auditable_type' => ShipmentException::class,
                'auditable_id' => $exception->id,
                'old_values' => ['shipment_status' => $oldStatus],
                'new_values' => [
                    'exception_type' => $exception->exception_type,
                    'severity' => $exception->severity,
                    'reason' => $exception->reason,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $exception;
        });
    }

    /**
     * Update exception status (OPEN -> INVESTIGATING -> RESOLVED)
     */
    public function updateExceptionStatus(ShipmentException $exception, User $resolver, string $newStatus, ?string $resolutionNotes = null, ?string $resumeShipmentStatus = 'in_transit'): ShipmentException
    {
        if (!in_array($newStatus, ['open', 'investigating', 'resolved', 'closed'])) {
            throw new InvalidArgumentException("Status exception tidak valid: {$newStatus}");
        }

        return DB::transaction(function () use ($exception, $resolver, $newStatus, $resolutionNotes, $resumeShipmentStatus) {
            $oldStatus = $exception->status;

            $updateData = [
                'status' => $newStatus,
                'resolution_notes' => $resolutionNotes ?: $exception->resolution_notes,
            ];

            if ($newStatus === 'resolved' || $newStatus === 'closed') {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = $resolver->id;

                // Resume shipment from 'exception' status back to operational status (e.g. in_transit)
                $shipment = $exception->shipment;
                if ($shipment && $shipment->status === 'exception') {
                    $shipment->update([
                        'status' => $resumeShipmentStatus ?: 'in_transit',
                    ]);

                    TrackingEvent::create([
                        'shipment_id' => $shipment->id,
                        'created_by' => $resolver->id,
                        'status' => $shipment->status,
                        'event_title' => 'Exception Resolved: Journey Resumed',
                        'description' => "Kendala telah terselesaikan ({$resolutionNotes}). Pengiriman dilanjutkan.",
                        'occurred_at' => now(),
                    ]);
                }
            }

            $exception->update($updateData);

            // Notify customer if resolved
            if ($newStatus === 'resolved' && $exception->shipment && $exception->shipment->user_id) {
                Notification::create([
                    'user_id' => $exception->shipment->user_id,
                    'shipment_id' => $exception->shipment->id,
                    'type' => 'success',
                    'title' => "Kendala Terselesaikan ({$exception->shipment->tracking_number})",
                    'message' => "Kendala pengiriman Anda telah berhasil ditangani dan perjalanan kargo telah dilanjutkan.",
                    'is_read' => false,
                ]);
            }

            // Audit Log
            AuditLog::create([
                'user_id' => $resolver->id,
                'action' => 'shipment.exception_updated',
                'auditable_type' => ShipmentException::class,
                'auditable_id' => $exception->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => $newStatus,
                    'resolution_notes' => $resolutionNotes,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $exception;
        });
    }
}

