<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class ReminderDispatch extends Model
{
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
