<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\ReminderDispatch;

class Appointment extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function reminderDispatches()
    {
        return $this->hasMany(ReminderDispatch::class);
    }
}
