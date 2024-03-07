<?php

namespace App\Traits;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

trait TokenAccessTrait
{


    public function getAccessToken(User $user, array $service): void
    {

            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])
                ->post(config('services.apiconsume.cliente_oauth'), [
                    'grant_type' => 'password',
                    'client_id' => config('services.apiconsume.client_id'),
                    'client_secret' => config('services.apiconsume.cliente_secret'),
                    'username' => request('email'),
                    'password' => request('password')
                ]);

            $access_token = $response->json();

            $user->accessToken()->create([
                    'service_id' => $service['data']['id'],
                    'access_token' => $access_token['access_token'],
                    'refresh_token' => $access_token['refresh_token'],
                    'expires_at' => now()->addSecond($access_token['expires_in'])]
            );

    }
}
