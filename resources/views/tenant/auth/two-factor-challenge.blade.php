@extends('tenant.auth.layout')

@section('title', __('Confirmation'))
@section('heading', '2FA')

@section('content')
    <div x-data="{ useRecovery: false }">

        <p class="lead" x-show="!useRecovery">
            {{ __('Please confirm access with your authentication code.') }}
        </p>
        <p class="lead" x-show="useRecovery" x-cloak>
            {{ __('Enter one of your backup codes to sign in.') }}
        </p>

        {{-- Authenticator-Code --}}
        <form method="POST" action="{{ route('two-factor.verify') }}" x-show="!useRecovery">
            @csrf
            <div class="field">
                <label for="code">{{ __('Authentication code') }}</label>
                <input type="text" name="code" id="code" class="code-input"
                       placeholder="······"
                       required autofocus autocomplete="one-time-code"
                       inputmode="numeric">
            </div>
            <button type="submit" class="btn">{{ __('Verify & continue') }}</button>
        </form>

        {{-- Backup-Code --}}
        <form method="POST" action="{{ route('two-factor.verify') }}" x-show="useRecovery" x-cloak>
            @csrf
            <div class="field">
                <label for="recovery_code">{{ __('Backup code') }}</label>
                <input type="text" name="recovery_code" id="recovery_code"
                       placeholder="XXXX-XXXX-XX"
                       required autocomplete="one-time-code">
            </div>
            <button type="submit" class="btn">{{ __('Use backup code') }}</button>
        </form>

        <div class="row" style="justify-content: center; gap: 1.5rem;">
            <button type="button" class="link-muted" @click="useRecovery = !useRecovery">
                <span x-show="!useRecovery">{{ __('Use backup code') }}</span>
                <span x-show="useRecovery" x-cloak>{{ __('Use authenticator code') }}</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="link-muted">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
@endsection
