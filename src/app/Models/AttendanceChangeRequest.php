<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceChangeRequest extends Model
{
    protected $fillable = [
        'attendance_id',
        'status',
        'approved_by',
        'requested_clock_in',
        'requested_clock_out',
        'requested_break_start',
        'requested_break_end',
        'requested_note',
    ];

    protected $casts =[
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(\App\Models\Attendance::class);
    }

    public function requestBreaks()
    {
        return $this->hasMany(\App\Models\AttendanceChangeRequestBreak::class, 'request_id');
    }
}
