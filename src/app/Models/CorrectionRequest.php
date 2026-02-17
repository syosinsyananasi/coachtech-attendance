<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_clock_in',
        'request_clock_out',
        'remark',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'request_clock_in' => 'datetime',
        'request_clock_out' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function correctionRequestRests()
    {
        return $this->hasMany(CorrectionRequestRest::class);
    }
}
