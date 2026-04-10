@php
    $isStreaming = (optional(auth()->user())->layout ?? 'classic') === 'streaming';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-3xl {{ $isStreaming ? 'text-white tracking-tighter italic' : 'text-gray-800 leading-tight' }}">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @if (session('status') === 'deletion-email-sent')
                        <div class="mb-6 p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-200 font-medium flex items-center gap-3">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ __('Eine Bestätigungs-E-Mail wurde an deine Adresse gesendet. Bitte klicke auf den Link in der Mail, um die Löschung abzuschließen.') }}
                        </div>
                    @endif
                    @include('tenant.profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('tenant.profile.partials.update-settings-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('tenant.profile.partials.two-factor-management')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('tenant.profile.partials.update-password-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-rose-500/10 shadow-2xl bg-rose-500/5">
                <div class="max-w-xl">
                    @include('tenant.profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
