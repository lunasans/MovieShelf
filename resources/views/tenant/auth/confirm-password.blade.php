@extends('tenant.auth.layout')

@section('title', __('Confirm Password'))
@section('heading', __('Confirm'))
@section('lead', __('This is a secure area. Please confirm your password before continuing.'))

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input type="password" name="password" id="password"
                   placeholder="••••••••" required autofocus autocomplete="current-password">
        </div>

        <button type="submit" class="btn">{{ __('Confirm') }}</button>
    </form>
@endsection
