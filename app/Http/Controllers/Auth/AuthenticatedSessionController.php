<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Traits\TokenAccessTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use TokenAccessTrait;

    public function __construct()
    {

    }

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
            ->post('http://127.0.0.1:8080/v1/login', [
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

        if (!$user->accessToken) {
            $this->getAccessToken($user, $service);
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
