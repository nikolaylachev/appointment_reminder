<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReminderDispatch;

class ReminderDispatchController extends Controller
{
    /**
     * Get reminders for user's clients
     *
     * @param Request $request
     * @param string $status - scheduled or sent
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $status)
    {
        if (!in_array($status, ['scheduled', 'sent'])) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $reminders = ReminderDispatch::where('status', $status)
            ->whereHas('appointment.client', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->orderBy('scheduled_for', 'desc')
            ->get();

        return response()->json([
            'reminders' => $reminders
        ]);
    }
}
