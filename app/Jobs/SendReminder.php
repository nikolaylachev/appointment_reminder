<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ReminderDispatch;
use Illuminate\Support\Facades\Log;

class SendReminder implements ShouldQueue
{
    use Queueable;

    public ReminderDispatch $reminder;

    /**
     * Create a new job instance.
     */
    public function __construct(ReminderDispatch $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running SendReminder job at ' . now());

        $this->reminder->sent_at = now();
        $this->reminder->status = 'sent';
        $this->reminder->save();

        Log::info('Reminder updated: ' . $this->reminder->fresh()->toJson());
    }
}
