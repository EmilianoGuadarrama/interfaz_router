<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WifiService;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (session('authenticated')) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        if ($request->password === 'nupnetadmin') {
            session(['authenticated' => true]);
            return redirect('/');
        }

        return back()->withErrors(['password' => 'La contraseña es incorrecta.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('authenticated');
        $request->session()->flush();
        return redirect()->route('login');
    }
}
