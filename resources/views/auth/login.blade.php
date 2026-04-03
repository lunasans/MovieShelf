<x-app-layout>
    <div class="min-h-[80vh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-10 py-12 glass rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden group">
            <!-- Decorative Gradient Background -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-rose-500/10 blur-[80px] rounded-full group-hover:bg-rose-500/20 transition-all duration-700"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-red-500/10 blur-[80px] rounded-full group-hover:bg-red-500/20 transition-all duration-700"></div>

            <div class="relative">
                <div class="flex flex-col items-center mb-10">
                    <div class="h-20 w-20 bg-gradient-to-br from-rose-600 to-red-700 rounded-3xl flex items-center justify-center shadow-xl shadow-rose-900/40 mb-6 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                        <i class="bi bi-person-fill-lock text-4xl text-white"></i>
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight text-center">
                        {{ __('Anmelden') }}
                    </h1>
                    <p class="mt-3 text-gray-400 text-sm text-center font-medium max-w-[280px]">
                        {{ __('Willkommen zurück! Bitte loggen Sie sich ein.') }}
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">{{ __('E-Mail Adresse') }}</label>
                        <input id="email" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none placeholder:text-gray-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2 ms-1">
                            <label for="password" class="block text-xs font-black uppercase tracking-widest text-gray-500">{{ __('Passwort') }}</label>
                        </div>
                        <input id="password" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none placeholder:text-gray-600"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex flex-col gap-5 mt-4">
                        <button class="w-full py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-900/40 transition-all transform active:scale-95">
                            {{ __('Anmelden') }}
                        </button>

                        <div class="flex items-center justify-between px-1">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                                <input id="remember_me" type="checkbox" class="rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50" name="remember">
                                <span class="ms-2 text-xs font-bold text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-widest">{{ __('Angemeldet bleiben') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-gray-500 hover:text-white transition-colors uppercase tracking-widest underline underline-offset-4 decoration-white/10 hover:decoration-white/30" href="{{ route('password.request') }}">
                                    {{ __('Vergessen?') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
