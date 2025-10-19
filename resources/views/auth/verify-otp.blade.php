@extends('layouts.auth')

@section('title', 'OTP Verification')

@section('content')
<div class="wrapper vh-100 bg-light">
    <div class="row align-items-center h-100">
        <form method="POST" action="{{ route('verify.otp') }}" class="col-lg-3 col-md-4 col-10 mx-auto text-center p-4 shadow rounded bg-white">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <h1 class="h6 mb-3">Enter OTP</h1>
            <div class="form-group mb-3">
                <input type="text" name="otp" class="form-control form-control-lg text-center" maxlength="6" placeholder="6-digit code" required autofocus>
            </div>
            <button class="btn btn-lg btn-primary btn-block w-100" type="submit">Verify</button>

            <p class="mt-4 text-muted">© 2025 HR3</p>
        </form>
    </div>
</div>
@endsection