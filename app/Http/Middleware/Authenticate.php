<?php

namespace App\Http\Middleware;

use App\Models\Key;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // 只有合法的 App id 和 App key 才能调用接口
        if($request->hasHeader('X-APP-ID') && $request->hasHeader('X-APP-KEY')) {

            $appId = $request->header('X-APP-ID');
            $appKey = $request->header('X-APP-KEY');

            $key = Key::where('app_id', $appId)
                ->where('app_key', $appKey)
                ->first();

            if(!empty($key)) {
                if($this->isTesting($request)) {
                    $request->attributes->add(['is_testing' => true]);
                } else {
                    $request->attributes->add(['is_testing' => false]);
                }
                $request->attributes->add(['partner_id' => $key['partner_id']]);

                return $next($request);
            }
        }

        throw new AuthenticationException();
    }

    private function isTesting(Request $request)
    {
        if($request->hasHeader('X-Testing') && $request->header('X-Testing') == 'true') {
            return true;
        }

        return false;
    }
}
