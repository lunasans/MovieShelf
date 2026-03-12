<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">{{ __('Email') }}</label>
            <input id="email" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <label for="password" class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">{{ __('Password') }}</label>
            <input id="password" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-600"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-4 mt-8">
            <button class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-500 hover:to-indigo-600 text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-900/40 transition-all transform active:scale-95">
                {{ __('Anmelden') }}
            </button>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" class="rounded-lg border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50" name="remember">
                    <span class="ms-2 text-xs font-bold text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-widest">{{ __('Angemeldet bleiben') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-gray-500 hover:text-white transition-colors uppercase tracking-widest" href="{{ route('password.request') }}">
                        {{ __('Passwort vergessen?') }}
                    </a>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
