<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class ReminderDispatch extends Model
{
    protected $fillable = [
        'appointment_id',
        'scheduled_for',
        'send_at',
        'status',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];
    

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
