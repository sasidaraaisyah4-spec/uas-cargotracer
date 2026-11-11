<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:open,investigating,resolved,closed',
            'resolution_notes' => 'nullable|string|max:1000',
            'resume_shipment_status' => 'nullable|in:in_transit,out_for_delivery,delivered,cancelled',
        ];
    }
}

