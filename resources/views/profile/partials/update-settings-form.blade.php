<section>
    <header>
        <h2 class="text-lg font-black text-white uppercase tracking-tight">
            {{ __('App Settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __("Manage your preferred language and dashboard layout.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- No hidden fields for name/email needed here anymore as we use a separate controller method --}}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Language Setting --}}
            <div>
                <x-input-label for="language" :value="__('Language')" class="text-gray-300 font-bold mb-2" />
                <select id="language" name="language" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500/50 text-white transition-all">
                    <option value="de" {{ old('language', $user->language) === 'de' ? 'selected' : '' }} class="bg-gray-900 text-white">Deutsch</option>
                    <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }} class="bg-gray-900 text-white">English</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('language')" />
            </div>

            {{-- Layout Setting --}}
            <div>
                <x-input-label for="layout" :value="__('Dashboard Layout')" class="text-gray-300 font-bold mb-2" />
                <select id="layout" name="layout" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500/50 text-white transition-all">
                    <option value="classic" {{ old('layout', $user->layout) === 'classic' ? 'selected' : '' }} class="bg-gray-900 text-white">{{ __('Classic') }}</option>
                    <option value="streaming" {{ old('layout', $user->layout) === 'streaming' ? 'selected' : '' }} class="bg-gray-900 text-white">{{ __('Streaming') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('layout')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-900/40">
                {{ __('Save Settings') }}
            </x-primary-button>

            @if (session('status') === 'settings-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400 font-bold"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
