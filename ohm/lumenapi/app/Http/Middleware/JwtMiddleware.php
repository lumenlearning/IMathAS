<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

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
            // Unauthorized response if token not there
            return response()->json([
                'errors' => ['Authorization header not provided']
            ], 401);
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if (!$jwt) {
            // Unauthorized response if token not there
            return response()->json([
                'errors' => ['Token not provided.']
            ], 401);
        }

        try {
            $credentials = JWT::decode($jwt, env('QUESTION_API_JWT_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'errors' => ['Provided token is expired.']
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'errors' => ['An error while decoding token.']
            ], 400);
        }

        return $next($request);
    }
}