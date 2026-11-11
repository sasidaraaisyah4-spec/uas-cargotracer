<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_event_id' => 'nullable|exists:tracking_events,id',
            'exception_type' => 'required|string|in:customs_hold,weather_delay,port_congestion,damaged,misrouted,documentation_issue,delay,failed_attempt,incorrect_address,other',
            'severity' => 'required|in:low,medium,high,critical',
            'reason' => 'required|string|max:1000',
            'resolution_notes' => 'nullable|string',
        ];
    }
}

