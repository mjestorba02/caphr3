<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginBackupController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        return view('auth.loginbackup');
    }

    protected function authenticated(Request $request, $user)
    {
        // Store photo or other session data if needed
        session(['user_photo' => $user->photo]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Wrong password or invalid credentials.');
    }
}