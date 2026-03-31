<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-settings-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.two-factor-management')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="glass p-8 rounded-[2rem] border border-rose-500/10 shadow-2xl bg-rose-500/5">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
