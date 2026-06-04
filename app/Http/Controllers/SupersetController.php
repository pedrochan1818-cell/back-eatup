<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SupersetController extends Controller
{
    public function guestToken(): JsonResponse
    {
        try {
            $baseUrl     = rtrim(config('services.superset.url'), '/');
            $username    = config('services.superset.username');
            $password    = config('services.superset.password');
            $dashboardId = (string) config('services.superset.dashboard_id');

            // 🥠 CookieJar para mantener la sesión entre requests
            $cookieJar = new CookieJar();

            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout'  => 10,
                'cookies'  => true, // muy importante
            ]);

            /**
             * 1) LOGIN A SUPERSET (JWT)
             */
            $loginResponse = $client->post('/api/v1/security/login', [
                'cookies' => $cookieJar,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'json' => [
                    'username' => $username,
                    'password' => $password,
                    'provider' => 'db',
                    'refresh'  => true,
                ],
            ]);

            $loginData   = json_decode($loginResponse->getBody()->getContents(), true);
            $accessToken = $loginData['access_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('No se pudo obtener access_token de Superset');
            }

            /**
             * 2) PEDIR CSRF TOKEN (usa el mismo cookie jar)
             */
            $csrfResponse = $client->get('/api/v1/security/csrf_token/', [
                'cookies' => $cookieJar,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            $csrfData  = json_decode($csrfResponse->getBody()->getContents(), true);
            $csrfToken = $csrfData['result'] ?? null;

            if (!$csrfToken) {
                throw new \Exception('No se pudo obtener csrf_token de Superset');
            }

            /**
             * 3) PEDIR GUEST TOKEN (mismo cliente + mismas cookies)
             */
            $guestResponse = $client->post('/api/v1/security/guest_token/', [
                'cookies' => $cookieJar,
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-CSRFToken'   => $csrfToken,
                ],
                'json' => [
                    'resources' => [
                        [
                            'type' => 'dashboard',
                            'id'   => $dashboardId, // ej. "37"
                        ],
                    ],
                    'user' => [
                        'username'   => 'eatup_embed',
                        'first_name' => 'EatUp',
                        'last_name'  => 'Admin',
                    ],
                    'rls' => [],
                ],
            ]);

            $guestData = json_decode($guestResponse->getBody()->getContents(), true);

            if (empty($guestData['token'])) {
                throw new \Exception('Superset no devolvió guest token');
            }

            return response()->json([
                'guest_token'  => $guestData['token'],
                'dashboard_id' => $dashboardId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error obteniendo guest token de Superset', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al obtener guest token de Superset',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }
}
