<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'start_time',
        'end_time',
        'rest1_start',
        'rest1_end',
        'rest2_start',
        'rest2_end',
        'reason',
        'status',
    ];

    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'rest1_start'  => 'datetime',
        'rest1_end'    => 'datetime',
        'rest2_start'  => 'datetime',
        'rest2_end'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];


    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
