<?php

namespace App\Http\Middleware;

use App\Models\Wallet;
use App\Traits\ResponseJsonTrait;
use Closure;

class CustomAuth
{
    use ResponseJsonTrait;

    public function handle($request, Closure $next)
    {
        $headerAuth = explode('Token ', $request->header('Authorization'));
        if (isset($headerAuth[1])) {
            $wallet = Wallet::where('api_token', $headerAuth[1])->first();
            if ($wallet) {
                return $next($request);
            }
        }

        return $this->responseError('unauthenticated', [
            'data' => ['message' => 'unauthenticated'],
        ]);
    }
}
