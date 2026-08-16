@extends('tenant.auth.layout')

@section('title', __('Log in'))
@section('heading', __('Log in'))
@section('lead', __('Welcome back! Please log in.'))

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">{{ __('Email address') }}</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input type="password" name="password" id="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
        </div>

        <button type="submit" class="btn">{{ __('Log in') }}</button>

        <div class="row">
            <label class="remember" for="remember_me">
                <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                <span>{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="link-muted" href="{{ route('password.request') }}">{{ __('Forgot?') }}</a>
            @endif
        </div>
    </form>
@endsection
