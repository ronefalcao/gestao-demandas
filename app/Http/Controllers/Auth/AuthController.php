<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('filament.admin.auth.login');
    }

    public function login(Request $request)
    {
        return redirect()
            ->route('filament.admin.auth.login')
            ->with('status', 'Realize o acesso pelo painel administrativo.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('filament.admin.auth.login');
    }
}



