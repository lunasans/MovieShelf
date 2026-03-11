<x-app-layout>
    <div class="min-h-[80vh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-10 py-12 glass rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden group">
            <!-- Decorative Gradient Background -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 blur-[80px] rounded-full group-hover:bg-blue-500/20 transition-all duration-700"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/10 blur-[80px] rounded-full group-hover:bg-purple-500/20 transition-all duration-700"></div>

            <div class="relative">
                <div class="flex flex-col items-center mb-10">
                    <div class="h-20 w-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center shadow-xl shadow-blue-900/40 mb-6 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                        <i class="bi bi-shield-lock-fill text-4xl text-white"></i>
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight text-center">
                        2FA {{ __('Verification') }}
                    </h1>
                    <p class="mt-3 text-gray-400 text-sm text-center font-medium max-w-[280px]">
                        {{ __('Please confirm access to your account by entering the authentication code.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-8">
                    @csrf

                    <div>
                        <x-input-label for="code" :value="__('Authentication Code')" class="text-center" />
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
                        <x-primary-button class="w-full justify-center py-4 text-base tracking-wider uppercase">
                            {{ __('Verify & Continue') }}
                        </x-primary-button>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full text-gray-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors py-2">
                                <i class="bi bi-box-arrow-left mr-1"></i> {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
