<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    // 退款状态
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';

    public function charge()
    {
        return $this->belongsTo('App\Models\Charge');
    }
}