<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Carbon\Carbon;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * When user logs in successfully, generate OTP and redirect to verification page.
     */
    protected function authenticated(Request $request, $user)
    {
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5); // expires in 5 mins
        $user->save();

        // Send OTP email
        Mail::to($user->email)->send(new SendOtpMail($otp));

        // Logout user temporarily
        Auth::logout();

        // Save user ID to session
        $request->session()->put('otp_user_id', $user->id);

        // Redirect to OTP verification page
        return redirect()->route('verify.otp.form')->with('info', 'An OTP has been sent to your email.');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Wrong password or invalid credentials.');
    }
}