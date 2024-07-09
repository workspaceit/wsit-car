<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;

class ApiLoginController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        if (Auth::attempt(['username' => request('username'), 'password' => request('password')])) {
            $oClient = OClient::where('password_client', 1)->latest()->first();
            return $this->getTokenAndRefreshToken($oClient, request('username'), request('password'));
        } else {
            return response()->json(["message"=> "Unauthorised", "errors" => ["username" => ["These credentials do not match our records."]]], 401);
        }
    }

    /**
     * @param OClient $oClient
     * @param         $email
     * @param         $password
     * @return JsonResponse
     */
    public function getTokenAndRefreshToken(OClient $oClient, $email, $password): JsonResponse
    {
        $http    = new Client(['verify' => false]);
        $oClient = OClient::where('password_client', 1)->latest()->first();

        try {
            $response = $http->request('POST', route('passport.token'), [
                'form_params' => [
                    'grant_type'    => 'password',
                    'client_id'     => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username'      => $email,
                    'password'      => $password,
                    'scope'         => '*',
                ]
            ]);
        } catch (\Throwable $throwable) {
            return response()->json(['status' => 'error', 'errors' => ['messages' => $throwable->getMessage()]], 200);
        }

        return response()->json(json_decode((string) $response->getBody(), true), 200);
    }
}
