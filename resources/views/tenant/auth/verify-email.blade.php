@extends('tenant.auth.layout')

@section('title', __('Verify email'))
@section('heading', __('Almost there'))
@section('lead', __('We sent you a verification link. Click it to get started. Did not arrive? We are happy to send it again.'))

@section('content')
    @if (session('status') === 'verification-link-sent')
        <div class="notice notice-success">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn">{{ __('Resend link') }}</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-ghost">{{ __('Log Out') }}</button>
    </form>
@endsection
