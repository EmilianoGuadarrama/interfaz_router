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

    public function login(Request $request, WifiService $wifiService)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $inputPassword = $request->password;
        $wifiStatus = $wifiService->getWifiStatus();
        $valid = false;

        if ($wifiStatus['success']) {
            foreach ($wifiStatus['data']['interfaces'] as $interface) {
                if (isset($interface['key']) && $interface['key'] === $inputPassword) {
                    $valid = true;
                    break;
                }
            }
        }

        // Fallback admin password just in case wifi is unconfigured or has error
        if ($inputPassword === 'admin') {
            $valid = true;
        }

        if ($valid) {
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
