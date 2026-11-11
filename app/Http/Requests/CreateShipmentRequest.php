<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlled by auth & role middleware
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'carrier_id' => 'nullable|exists:carriers,id',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|different:origin_location_id|exists:locations,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:30',
            'sender_address' => 'required|string',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_address' => 'required|string',
            'cargo_type' => 'nullable|string|max:100',
            'service_type' => 'required|in:regular,express,same_day,cargo,economy',
            'total_price' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|string|in:paid,unpaid',
            'notes' => 'nullable|string',
            'expected_departure_at' => 'nullable|date',
            'expected_arrival_at' => 'nullable|date|after_or_equal:expected_departure_at',
            'estimated_delivery_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_category' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'required|numeric|min:0.01',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.width' => 'nullable|numeric|min:0',
            'items.*.height' => 'nullable|numeric|min:0',
            'items.*.declared_value' => 'nullable|numeric|min:0',
            'items.*.is_hazardous' => 'nullable|boolean',
            'items.*.description' => 'nullable|string',
        ];
    }
}

