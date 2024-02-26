<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     * @param LoginRequest $request
     */
    public function store(LoginRequest $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])
            ->post('http://127.0.0.1:8001/v1/login', [
                'email' => $request->email,
                'password' => $request->password
            ]);

        if ($response->status() == 404) {
            return back()->withErrors(['generic' => 'Las credenciales no coinciden con nuestros datos']);
        }

        $service = $response->json();

        $user = User::updateOrCreate(
            ['email' => $request->email],
            $service['data']
        );

        if(!$user->accessToken) {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])
                ->post('http://127.0.0.1:8001/oauth/token', [
                    'grant_type' => 'password',
                    'client_id' => '99cb1a2a-1696-40d7-be26-1f7826f78960',
                    'client_secret' => 'Ptpe5aVljf5lOowTlbSNHCVyqVFmrhyNmrzHrZg0',
                    'username' => $request->email,
                    'password' => $request->password
                ]);

            $access_token = $response->json();

            $user->accessToken()->create([
                    'service_id' => $service['data']['id'],
                    'access_token' => $access_token['access_token'],
                    'refresh_token' => $access_token['refresh_token'],
                    'expires_at' => now()->addSecond($access_token['expires_in'])]
            );
        }

        Auth::login($user, $request->remember);

        return redirect()->intended(RouteServiceProvider::HOME);


    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
