<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => 'nullable|exists:locations,id',
            'status' => 'nullable|string|in:created,booked,picked_up,in_transit,arrived_at_facility,departed_from_facility,out_for_delivery,delivered,exception,cancelled',
            'event_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'occurred_at' => 'nullable|date',
            'estimated_delivery_at' => 'nullable|date',
        ];
    }
}

