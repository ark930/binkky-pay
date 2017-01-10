<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $primaryKey = 'partner_id';
    protected $hidden = [
        'deleted_at',
    ];
}