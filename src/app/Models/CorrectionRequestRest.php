<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequestRest extends Model
{
    protected $fillable = [
        'correction_request_id',
        'rest_id',
        'request_rest_start',
        'request_rest_end',
    ];

    protected $casts = [
        'request_rest_start' => 'datetime',
        'request_rest_end' => 'datetime',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }

    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }
}
