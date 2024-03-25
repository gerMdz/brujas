<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function resolveAuthorization()
    {
        if (auth()->user()->accessToken->expires_at <= now()) {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])
                ->post(config('services.apiconsume.cliente_oauth'), [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => auth()->user()->accessToken->refresh_token,
                    'client_id' => config('services.apiconsume.client_id'),
                    'client_secret' => config('services.apiconsume.cliente_secret'),


                ]);

            $access_token = $response->json();

            auth()->user()->accessToken->update([
                'access_token' => $access_token['access_token'],
                'refresh_token' => $access_token['refresh_token'],
                'expires_at' => now()->addSecond($access_token['expires_in'])
            ]);
        }
    }

}
