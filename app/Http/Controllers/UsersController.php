<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        return Inertia::render('Users');
    }

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle login for both API (JSON) and web form requests.
     * For JSON requests, returns a Sanctum personal access token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput();
        }

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $abilities = $user->role === 'admin' ? ['admin'] : ['passenger'];

        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'token' => $token,
                'user'  => $user,
            ]);
        }

        $request->session()->put('api_token', $token);

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
