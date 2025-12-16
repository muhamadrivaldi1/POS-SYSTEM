<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('username', 'password');
        
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['username' => 'Username tidak ditemukan'])
                ->withInput();
        }

        if ($user->status !== 'active') {
            return redirect()->back()
                ->withErrors(['username' => 'Akun Anda tidak aktif. Hubungi administrator'])
                ->withInput();
        }

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            activity()
                ->causedBy($user)
                ->log('User login: ' . $user->username);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . $user->full_name);
        }

        return redirect()->back()
            ->withErrors(['password' => 'Password salah'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        activity()
            ->causedBy(Auth::user())
            ->log('User logout: ' . Auth::user()->username);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah logout');
    }
}