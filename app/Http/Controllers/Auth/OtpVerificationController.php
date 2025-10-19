<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    public function showVerifyForm()
    {
        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = User::find(session('otp_user_id'));

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        if ($user->otp !== $request->otp) {
            return back()->with('error', 'Invalid OTP code.');
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return back()->with('error', 'OTP has expired. Please log in again.');
        }

        // Clear OTP fields
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Log user in
        Auth::login($user);

        // Clear session
        session()->forget('otp_user_id');

        return redirect('/dashboard')->with('success', 'Login successful.');
    }
}