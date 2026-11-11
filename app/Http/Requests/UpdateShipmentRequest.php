<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'nullable|exists:carriers,id',
            'status' => 'nullable|string|in:created,booked,picked_up,in_transit,out_for_delivery,exception,delivered,cancelled',
            'notes' => 'nullable|string',
            'estimated_delivery_at' => 'nullable|date',
            'payment_status' => 'nullable|string|in:paid,unpaid',
        ];
    }
}

