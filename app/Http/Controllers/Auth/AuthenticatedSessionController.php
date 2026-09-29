<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        // Sengaja TIDAK pakai redirect()->intended(): kalau ada URL "intended" tersimpan
        // dari sebelum login (mis. orang sempat coba buka halaman yang butuh role tertentu
        // di tab/sesi yang sama sebelum login), redirect ke situ bisa membentur middleware
        // role dan malah menampilkan 403 tepat setelah login berhasil. Dashboard sudah tahu
        // cara mengarahkan tiap role ke halamannya masing-masing, jadi selalu ke sana saja.
        $request->session()->forget('url.intended');

        return redirect()->route('dashboard');
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