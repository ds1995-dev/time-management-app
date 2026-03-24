<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceChangeRequestBreak extends Model
{
    protected $fillable = [
        'request_id',
        'requested_break_start',
        'requested_break_end',
    ];

    protected $casts = [
        'requested_break_start'=> 'datetime',
        'requested_break_end' => 'datetime',
    ];
}
