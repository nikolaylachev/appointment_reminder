<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ReminderDispatch;
use App\Jobs\SendReminder;

class AppointmentController extends Controller
{
    /**
     * Create an appointment and associate a client with it
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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


        $reminderTime = \Carbon\Carbon::parse($appointment->start_time, $appointment->timezone)->subMinutes(1)->timezone('UTC');

        // create ReminderDispatch record
        $reminder = ReminderDispatch::create([
            'appointment_id' => $appointment->id,
            'scheduled_for' => $reminderTime,
            'status' => 'pending',
        ]);

        // dispatch the job with delay
        SendReminder::dispatch($reminder)->delay($reminderTime);        

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
     * @return \Illuminate\Http\JsonResponse
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

    /**
     * Get specific appointment by id
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $appointment = Appointment::where('id', $id)
            ->whereHas('client', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->first();

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found or unauthorized'], 403);
        }

        return response()->json([
            'appointment' => $appointment
        ]);
    }

    /**
     * Update appointment.
     * Can't update the client of the appointment
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::where('id', $id)
            ->whereHas('client', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->first();

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found or unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_time' => 'sometimes|date',
            'timezone' => 'sometimes|string|timezone',
            'recurrence' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Delete an appointment
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $appointment = Appointment::where('id', $id)
            ->whereHas('client', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->first();

        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found or unauthorized'], 403);
        }

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully'
        ]);
    }
}
