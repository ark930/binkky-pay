<?php

namespace App\Http\Controllers;


use App\Models\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function store(Request $request)
    {
        $key = new Key();
        $key['app_id'] = 'bk_' . strtolower(str_random(15));
        $key['app_key'] = strtolower(str_random(32));
        $key->save();

        return Key::find($key['partner_id']);
    }

    public function show(Request $request, $partner_id)
    {
        $key = Key::findOrFail($partner_id);

        return $key;
    }

    public function update(Request $request, $partner_id)
    {
        $key = Key::findOrFail($partner_id);

        return $key;
    }
}