<?php

namespace App\Http\Controllers\Channels;

use App\Http\Controllers\Controller;
use App\Models\Channel\Wechat;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function store(Request $request)
    {
        $partnerId = $request->get('partner_id');
        $channel = Wechat::where('partner_id', $partnerId)->first();

        if(empty($channel)) {
            throw new ModelNotFoundException();
        }

        return $channel;
    }

    public function show(Request $request)
    {
        $partnerId = $request->get('partner_id');
        $channel = Wechat::where('partner_id', $partnerId)->first();

        if(empty($channel)) {
            throw new ModelNotFoundException();
        }

        return $channel;
    }
}