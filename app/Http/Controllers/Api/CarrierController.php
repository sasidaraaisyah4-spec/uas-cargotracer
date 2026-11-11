<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index(): JsonResponse
    {
        $carriers = Carrier::where('status', 'active')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $carriers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrator can manage carriers.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:carriers,code',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $carrier = Carrier::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Carrier created successfully',
            'data' => $carrier,
        ], 201);
    }
}

