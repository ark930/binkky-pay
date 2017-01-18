<?php

namespace app\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class MerchantApiAuth
{

    public function handle($request, Closure $next)
    {
        if($request->hasHeader('X-PAYMENT-KEY')
            && $request->header('X-PAYMENT-KEY') === env('PAYMENT_KEY')) {

            return $next($request);
        }

        throw new AuthenticationException();
    }
}