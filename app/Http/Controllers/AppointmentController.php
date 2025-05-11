<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'start_time' => 'required|date',
            'timezone' => 'required|string|timezone',
            'recurrence' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Ensure the client belongs to the authenticated user
        $client = $request->user()->clients()->find($validated['client_id']);

        if (! $client) {
            return response()->json(['message' => 'Unauthorized client'], 403);
        }

        $appointment = $client->appointments()->create([
            'start_time' => $validated['start_time'],
            'timezone' => $validated['timezone'],
            'recurrence' => $validated['recurrence'],
            'notes' => $validated['notes'],
        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment,
        ], 201);
    }
}
