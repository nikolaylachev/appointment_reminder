<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    /**
     * Associate a client with an appointment
     *
     * @param Request $request
     * @return void
     */
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

    /**
     * Get upcoming or past appointments
     *
     * @param Request $request
     * @param string $status
     * @return void
     */
    public function filterByStatus(Request $request, $status)
    {
        $now = now();

        $query = Appointment::whereHas('client', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });

        if ($status === 'upcoming') {
            $query->where('start_time', '>=', $now);
        } elseif ($status === 'past') {
            $query->where('start_time', '<', $now);
        } else {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $appointments = $query->orderBy('start_time')->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }
}
