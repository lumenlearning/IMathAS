<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @OA\Post(
     *     path="/token",
     *     @OA\Response(
     *         response="200",
     *         description="Returns some sample category things",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Error: Unauthorized. When supplied credentials are invalid.",
     *     )
     * )
     */
    public function GetToken(Request $request): JsonResponse
    {
        $this->validate($request, [
                'client_id' => 'required',
                'client_secret' => 'required']
        );

        if (!($request->all()['client_id'] == env('LONG_LIVED_CLIENT_ID') ||
            $request->all()['client_secret'] == env('LONG_LIVED_CLIENT_SECRET'))) {

            return response()->json([
                'errors' => ['Invalid credentials'],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + env('JWT_EXPIRY');
        $payload = array(
            'userid' => 1,
            'iat' => $issuedAt,
            'exp' => $expirationTime
        );
        $key = env('JWT_SECRET');
        $jwt = JWT::encode($payload, $key, 'HS256');

        return response()->json(['access_token' => $jwt, 'token_type' => 'Bearer']);
    }
}