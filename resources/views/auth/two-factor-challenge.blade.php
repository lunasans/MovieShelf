<x-app-layout>
    <div class="min-h-[80vh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-10 py-12 glass rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden group"
             x-data="{ useRecovery: false }">
            <!-- Decorative Gradient Background -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-rose-500/10 blur-[80px] rounded-full group-hover:bg-rose-500/20 transition-all duration-700"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-red-500/10 blur-[80px] rounded-full group-hover:bg-red-500/20 transition-all duration-700"></div>

            <div class="relative">
                <div class="flex flex-col items-center mb-10">
                    <div class="h-20 w-20 bg-gradient-to-br from-rose-600 to-red-700 rounded-3xl flex items-center justify-center shadow-xl shadow-rose-900/40 mb-6 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                        <i class="bi bi-shield-lock-fill text-4xl text-white"></i>
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight text-center">
                        2FA {{ __('Verifizierung') }}
                    </h1>
                    <p class="mt-3 text-gray-400 text-sm text-center font-medium max-w-[280px]" x-show="!useRecovery">
                        {{ __('Bitte bestätigen Sie den Zugriff mit Ihrem Authentifizierungscode.') }}
                    </p>
                    <p class="mt-3 text-gray-400 text-sm text-center font-medium max-w-[280px]" x-show="useRecovery" x-cloak>
                        {{ __('Geben Sie einen Ihrer Backup-Codes ein, um sich anzumelden.') }}
                    </p>
                </div>

                {{-- OTP Code Form --}}
                <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-8" x-show="!useRecovery">
                    @csrf

                    <div>
                        <x-input-label for="code" :value="__('Authentifizierungscode')" class="text-center" />
                        <x-text-input id="code" class="block mt-2 w-full text-center text-3xl font-black tracking-[0.5em] py-4 placeholder:tracking-normal placeholder:font-normal placeholder:text-lg"
                                     type="text"
                                     name="code"
                                     required
                                     autofocus
                                     autocomplete="one-time-code"
                                     placeholder="······" />
                        <x-input-error :messages="$errors->get('code')" class="mt-3 text-center font-bold" />
                    </div>

                    <div class="flex flex-col gap-4">
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-900/40 transition-all transform active:scale-95 text-base">
                            {{ __('Verifizieren & Fortfahren') }}
                        </button>
                    </div>
                </form>

                {{-- Recovery Code Form --}}
                <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-8" x-show="useRecovery" x-cloak>
                    @csrf

                    <div>
                        <x-input-label for="recovery_code" :value="__('Backup-Code')" class="text-center" />
                        <x-text-input id="recovery_code" class="block mt-2 w-full text-center text-xl font-black tracking-widest py-4 placeholder:tracking-normal placeholder:font-normal placeholder:text-base"
                                     type="text"
                                     name="recovery_code"
                                     required
                                     autocomplete="one-time-code"
                                     placeholder="XXXX-XXXX-XX" />
                        <x-input-error :messages="$errors->get('recovery_code')" class="mt-3 text-center font-bold" />
                    </div>

                    <div class="flex flex-col gap-4">
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-900/40 transition-all transform active:scale-95 text-base">
                            {{ __('Backup-Code verwenden') }}
                        </button>
                    </div>
                </form>

                <div class="flex flex-col gap-4 mt-4">
                    <button type="button" @click="useRecovery = !useRecovery"
                        class="w-full text-gray-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors py-2">
                        <span x-show="!useRecovery">
                            <i class="bi bi-key mr-1"></i> {{ __('Backup-Code verwenden') }}
                        </span>
                        <span x-show="useRecovery" x-cloak>
                            <i class="bi bi-phone mr-1"></i> {{ __('Authenticator-Code verwenden') }}
                        </span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full text-gray-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors py-2">
                            <i class="bi bi-box-arrow-left mr-1"></i> {{ __('Abmelden') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>