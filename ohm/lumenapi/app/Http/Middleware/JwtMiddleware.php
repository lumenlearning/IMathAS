<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $authHeader = $request->header('authorization');
        if (!$authHeader) {
            Log::error('Authorization header not provided');
            return response()->json([
                'errors' => ['Authorization header not provided']
            ], 401);
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if (!$jwt) {
            Log::error('Token not provided');
            return response()->json([
                'errors' => ['Token not provided.']
            ], 401);
        }

        try {
            $key = new Key(env('QUESTION_API_JWT_SECRET'), 'HS256');
            $credentials = JWT::decode($jwt, $key);
        } catch (ExpiredException $e) {
            Log::error($e);
            return response()->json([
                'errors' => ['Provided token is expired.']
            ], 400);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'errors' => ['An error while decoding token.']
            ], 400);
        }

        return $next($request);
    }
}
