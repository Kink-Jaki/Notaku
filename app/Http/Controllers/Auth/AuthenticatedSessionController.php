<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        session()->flash('login_success', 'Selamat datang, '.$user->name.'!');

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === User::ROLE_KASIR) {
            return redirect()->route('kasir.dashboard');
        }

        return redirect()->route('marketplace');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        session()->flash('logout_success', 'Anda telah keluar');

        return redirect('/');
    }
}
