<x-app-layout>
    {{-- Kein <x-slot name="header">: layouts/app.blade.php gibt $header nicht
         aus, die Ueberschrift wurde dadurch nie dargestellt. Sie steht jetzt
         im Seiteninhalt. --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">{{ __('Profile') }}</h1>
                <p class="text-sm text-white/40 font-medium mt-1">
                    {{ __('Manage your account, security and data.') }}
                </p>
            </div>

            @if (session('status') === 'deletion-email-sent')
                <div class="p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-200 font-medium flex items-center gap-3">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ __('A confirmation email has been sent to your address. Please click the link in that email to complete the deletion.') }}
                </div>
            @endif

            {{-- Ab lg zweispaltig: der Inhalt der Karten war auf max-w-xl
                 begrenzt, wodurch auf breiten Schirmen rechts rund 700px leer
                 blieben und die Seite unnoetig lang wurde. --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                    @include('tenant.profile.partials.update-profile-information-form')
                </div>

                <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                    @include('tenant.profile.partials.update-settings-form')
                </div>

                <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                    @include('tenant.profile.partials.two-factor-management')
                </div>

                <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-2xl">
                    @include('tenant.profile.partials.update-password-form')
                </div>
            </div>

            {{-- Loeschbereich bewusst ueber die volle Breite und abgesetzt --}}
            <div class="glass p-8 rounded-[2rem] border border-rose-500/20 shadow-2xl bg-rose-500/5">
                <div class="max-w-2xl">
                    @include('tenant.profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
