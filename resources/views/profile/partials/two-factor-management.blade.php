<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
            <i class="bi bi-shield-check text-emerald-400"></i>
            {{ __('Zwei-Faktor-Authentifizierung') }}
        </h2>

        <p class="mt-2 text-sm text-gray-400 font-medium">
            {{ __('Fügen Sie Ihrem Konto mit Zwei-Faktor-Authentifizierung zusätzliche Sicherheit hinzu.') }}
        </p>
    </header>

    @if (!$user->hasTwoFactorEnabled())
        @if ($qrCodeSvg)
            <div class="space-y-6">
                <div class="p-4 bg-white rounded-2xl w-fit shadow-inner">
                    {!! $qrCodeSvg !!}
                </div>

                <div class="max-w-xl text-sm text-gray-300 bg-white/5 p-4 rounded-xl border border-white/10">
                    <p class="font-bold mb-2 text-rose-400 uppercase tracking-widest text-xs">
                        <i class="bi bi-info-circle mr-1"></i> {{ __('Einrichtungsanleitung') }}
                    </p>
                    {{ __('Scannen Sie den QR-Code mit Ihrer Authenticator-App und geben Sie den generierten Code ein, um die Zwei-Faktor-Authentifizierung zu aktivieren.') }}
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf
                    <div class="max-w-xs">
                        <x-input-label for="code" :value="__('Bestätigungscode')" />
                        <x-text-input id="code" name="code" type="text" class="block w-full" autofocus autocomplete="one-time-code" placeholder="000000" />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>
                            {{ __('2FA bestätigen') }}
                        </x-primary-button>

                        <x-secondary-button @click.prevent="window.location.reload()">
                            {{ __('Abbrechen') }}
                        </x-secondary-button>
                    </div>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <x-primary-button>
                    {{ __('2FA aktivieren') }}
                </x-primary-button>
            </form>
        @endif
    @else
        <div class="flex items-center gap-4 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
            <div class="h-10 w-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                <i class="bi bi-patch-check-fill text-xl"></i>
            </div>
            <div>
                <p class="text-white font-bold">{{ __('2FA ist aktiviert') }}</p>
                <p class="text-xs text-emerald-400/70">{{ __('Ihr Konto ist mit einer zusätzlichen Sicherheitsebene geschützt.') }}</p>
            </div>
        </div>

        {{-- Recovery Codes Section --}}
        <div x-data="{ showCodes: false }" class="space-y-4">
            <div class="flex items-center gap-3">
                <button @click="showCodes = !showCodes" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-gray-300 hover:text-white hover:bg-white/10 transition-all">
                    <i class="bi bi-key-fill text-amber-400"></i>
                    <span x-text="showCodes ? '{{ __('Backup-Codes ausblenden') }}' : '{{ __('Backup-Codes anzeigen') }}'"></span>
                </button>

                <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-sm font-bold text-amber-400 hover:bg-amber-500/20 transition-all">
                        <i class="bi bi-arrow-repeat"></i>
                        {{ __('Neue Codes generieren') }}
                    </button>
                </form>
            </div>

            <div x-show="showCodes" x-transition class="space-y-3">
                @php
                    $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
                @endphp

                @if (count($recoveryCodes) > 0)
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-triangle-fill text-amber-400"></i>
                            <p class="text-sm font-bold text-amber-400">
                                {{ __('Bewahren Sie diese Codes sicher auf!') }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">
                            {{ __('Diese Codes können jeweils nur einmal verwendet werden, falls Sie keinen Zugriff auf Ihre Authenticator-App haben.') }}
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($recoveryCodes as $code)
                                <div class="bg-gray-900/50 border border-white/5 rounded-lg px-3 py-2 text-center">
                                    <code class="text-sm font-mono font-bold text-white tracking-wider">{{ $code }}</code>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            <i class="bi bi-info-circle mr-1"></i>
                            {{ count($recoveryCodes) }} {{ __('Codes verbleibend') }}
                        </p>
                    </div>
                @else
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                        <p class="text-sm text-red-400 font-bold">
                            <i class="bi bi-exclamation-octagon-fill mr-1"></i>
                            {{ __('Keine Backup-Codes vorhanden. Bitte generieren Sie neue Codes.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <x-danger-button>
                {{ __('2FA deaktivieren') }}
            </x-danger-button>
        </form>
    @endif

    {{-- Show recovery codes after initial confirmation --}}
    @if (session('recoveryCodes'))
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-amber-500/20 rounded-xl flex items-center justify-center text-amber-400">
                    <i class="bi bi-key-fill text-xl"></i>
                </div>
                <div>
                    <p class="text-white font-bold">{{ __('Ihre Backup-Codes') }}</p>
                    <p class="text-xs text-amber-400/70">{{ __('Speichern Sie diese Codes an einem sicheren Ort. Sie werden nur einmal angezeigt!') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach (session('recoveryCodes') as $code)
                    <div class="bg-gray-900/50 border border-amber-500/10 rounded-lg px-3 py-2 text-center">
                        <code class="text-sm font-mono font-bold text-white tracking-wider">{{ $code }}</code>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-amber-400/60">
                <i class="bi bi-exclamation-triangle mr-1"></i>
                {{ __('Jeder Code kann nur einmal verwendet werden.') }}
            </p>
        </div>
    @endif

    @if (session('status') === 'two-factor-confirmed')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="text-sm text-emerald-400 font-bold">
            <i class="bi bi-check-circle-fill mr-1"></i> {{ __('Zwei-Faktor-Authentifizierung bestätigt und aktiviert.') }}
        </p>
    @endif

    @if (session('status') === 'recovery-codes-regenerated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="text-sm text-amber-400 font-bold">
            <i class="bi bi-check-circle-fill mr-1"></i> {{ __('Neue Backup-Codes wurden generiert.') }}
        </p>
    @endif
</section>
