@extends('tenant.auth.layout')

@section('title', __('Forgot password'))
@section('heading', __('Forgot password'))
@section('lead', __('No problem. Enter your email address and we will send you a reset link.'))

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">{{ __('Email address') }}</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username">
        </div>

        <button type="submit" class="btn">{{ __('Request link') }}</button>
    </form>
@endsection

@section('hint')
    <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
@endsection
