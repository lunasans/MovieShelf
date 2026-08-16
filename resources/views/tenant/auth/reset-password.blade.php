@extends('tenant.auth.layout')

@section('title', __('New Password'))
@section('heading', __('New Password'))
@section('lead', __('Choose a new password for your account.'))

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field">
            <label for="email">{{ __('Email address') }}</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">{{ __('New Password') }}</label>
            <input type="password" name="password" id="password"
                   placeholder="••••••••" required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   placeholder="••••••••" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn">{{ __('Save password') }}</button>
    </form>
@endsection

@section('hint')
    <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
@endsection
