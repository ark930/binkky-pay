<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    const STATUS_PENDING= 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_CLOSED = 'closed';

    public function refunds()
    {
        return $this->hasMany('App\Models\Refund');
    }
}