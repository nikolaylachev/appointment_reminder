<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\ReminderDispatch;
use App\Jobs\SendReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRecurringAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate future instances of recurring appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Command '{$this->signature}' started at " . now());

        $now = Carbon::now();

        $recurringAppointments = Appointment::with('client')
            ->whereNotNull('recurrence')
            ->where(function ($query) use ($now) {
                $query->whereNull('repeat_until')
                      ->orWhere('repeat_until', '>', $now);
            })
            ->get();

        foreach ($recurringAppointments as $appointment) {
            $lastStart = Carbon::parse($appointment->start_time, $appointment->timezone);
            $nextStart = match ($appointment->recurrence) {
                'weekly' => $lastStart->copy()->addWeek(),
                'monthly' => $lastStart->copy()->addMonth(),
                default => null,
            };

            if (! $nextStart) {
                $this->warn("Invalid recurrence for appointment ID {$appointment->id}");
                continue;
            }

            //Skip if the next date is in the past or next date is after the appointment's repeat_until field
            if (
                $nextStart->isPast() 
                || ($appointment->repeat_until 
                    && $nextStart->gt(Carbon::parse($appointment->repeat_until, $appointment->timezone))) 
            ) { 
                continue;
            }

            $exists = Appointment::where('client_id', $appointment->client_id)
                ->where('start_time', $nextStart->toDateTimeString())
                ->exists();

            if ($exists) {
                continue;
            }

            $newAppointment = $appointment->client->appointments()->create([
                'start_time' => $nextStart->toDateTimeString(),
                'timezone' => $appointment->timezone,
                'recurrence' => $appointment->recurrence,
                'notes' => $appointment->notes,
                'repeat_until' => $appointment->repeat_until,
                'reminder_offset' => $appointment->reminder_offset,
            ]);

            $reminderTime = $nextStart->copy()
                ->subMinutes($newAppointment->reminder_offset)
                ->timezone('UTC');

            $reminder = ReminderDispatch::create([
                'appointment_id' => $newAppointment->id,
                'scheduled_for' => $reminderTime,
                'status' => 'scheduled',
            ]);

            SendReminder::dispatch($reminder)->delay($reminderTime);

            $this->info("Created recurring appointment ID {$newAppointment->id} for client {$appointment->client_id}");

            Log::info("Created recurring appointment ID {$newAppointment->id} for client {$appointment->client_id} " . now());
        }

        return Command::SUCCESS;
    }
}
