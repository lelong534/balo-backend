<?php

namespace App\Http\Middleware;

use App\Enums\ApiStatusCode;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTExceptions;
use Illuminate\Support\Facades\Validator;


class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validatorRequire = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validatorRequire->fails()) {
            return response()->json([
                "code" => ApiStatusCode::PARAMETER_NOT_ENOUGH,
                "message" => "Token required"
            ]);
        }

        $token = $request->token;

        $request->headers->set("Authorization", "Bearer " . $token);

        return $next($request);
    }
}
