<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
            <i class="bi bi-shield-check text-emerald-400"></i>
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-2 text-sm text-gray-400 font-medium">
            {{ __('Add additional security to your account using two-factor authentication.') }}
        </p>
    </header>

    @if (!$user->hasTwoFactorEnabled())
        @if ($qrCodeSvg)
            <div class="space-y-6">
                <div class="p-4 bg-white rounded-2xl w-fit shadow-inner">
                    {!! $qrCodeSvg !!}
                </div>

                <div class="max-w-xl text-sm text-gray-300 bg-white/5 p-4 rounded-xl border border-white/10">
                    <p class="font-bold mb-2 text-blue-400 uppercase tracking-widest text-xs">
                        <i class="bi bi-info-circle mr-1"></i> {{ __('Setup Instructions') }}
                    </p>
                    {{ __('To finish enabling two-factor authentication, scan the following QR code using your phone\'s authenticator application and enter the generated OTP code.') }}
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf
                    <div class="max-w-xs">
                        <x-input-label for="code" :value="__('Confirmation Code')" />
                        <x-text-input id="code" name="code" type="text" class="block w-full" autofocus autocomplete="one-time-code" placeholder="000000" />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>
                            {{ __('Confirm 2FA') }}
                        </x-primary-button>

                        <x-secondary-button @click.prevent="window.location.reload()">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                    </div>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <x-primary-button>
                    {{ __('Enable 2FA') }}
                </x-primary-button>
            </form>
        @endif
    @else
        <div class="flex items-center gap-4 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
            <div class="h-10 w-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                <i class="bi bi-patch-check-fill text-xl"></i>
            </div>
            <div>
                <p class="text-white font-bold">{{ __('2FA is enabled') }}</p>
                <p class="text-xs text-emerald-400/70">{{ __('Your account is protected with an additional security layer.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <x-danger-button>
                {{ __('Disable 2FA') }}
            </x-danger-button>
        </form>
    @endif

    @if (session('status') === 'two-factor-confirmed')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-400 font-bold">
            <i class="bi bi-check-circle-fill mr-1"></i> {{ __('Two-factor authentication confirmed and enabled.') }}
        </p>
    @endif
</section>
