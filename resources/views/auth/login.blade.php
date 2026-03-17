<x-guest-layout>
    <div class="relative">
        <!-- Decorative Gradient Background inside the guest layout container -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 blur-[80px] rounded-full group-hover:bg-blue-500/20 transition-all duration-700"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/10 blur-[80px] rounded-full group-hover:bg-purple-500/20 transition-all duration-700"></div>

        <div class="relative">
            <div class="flex flex-col items-center mb-10">
                <div class="h-20 w-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center shadow-xl shadow-blue-900/40 mb-6 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                    <i class="bi bi-person-fill-lock text-4xl text-white"></i>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight text-center">
                    {{ __('Log in') }}
                </h1>
                <p class="mt-3 text-gray-400 text-sm text-center font-medium max-w-[280px]">
                    {{ __('Welcome back! Please log in to continue.') }}
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">{{ __('Email') }}</label>
                    <input id="email" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2 ms-1">
                        <label for="password" class="block text-xs font-black uppercase tracking-widest text-gray-500">{{ __('Passwort') }}</label>
                    </div>
                    <input id="password" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-600"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-col gap-5 mt-4">
                    <button class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-500 hover:to-indigo-600 text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-900/40 transition-all transform active:scale-95">
                        {{ __('Anmelden') }}
                    </button>

                    <div class="flex items-center justify-between px-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox" class="rounded-lg border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50" name="remember">
                            <span class="ms-2 text-xs font-bold text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-widest">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs font-bold text-gray-500 hover:text-white transition-colors uppercase tracking-widest underline underline-offset-4 decoration-white/10 hover:decoration-white/30" href="{{ route('password.request') }}">
                                {{ __('Forgot?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
