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
                <select id="language" name="language" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-rose-500/50 text-white transition-all">
                    <option value="de" {{ old('language', $user->language) === 'de' ? 'selected' : '' }} class="bg-gray-900 text-white">Deutsch</option>
                    <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }} class="bg-gray-900 text-white">English</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('language')" />
            </div>

            {{-- Layout Setting --}}
            <div>
                <x-input-label for="layout" :value="__('Dashboard Layout')" class="text-gray-300 font-bold mb-2" />
                <select id="layout" name="layout" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-rose-500/50 text-white transition-all">
                    <option value="classic" {{ old('layout', $user->layout) === 'classic' ? 'selected' : '' }} class="bg-gray-900 text-white">{{ __('Classic') }}</option>
                    <option value="streaming" {{ old('layout', $user->layout) === 'streaming' ? 'selected' : '' }} class="bg-gray-900 text-white">{{ __('Streaming') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('layout')" />
            </div>
        </div>

        {{-- Serien-Benachrichtigungen --}}
        <label for="notify_new_episodes" class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/10 cursor-pointer hover:border-rose-500/40 transition-all">
            <input type="checkbox" name="notify_new_episodes" id="notify_new_episodes" value="1"
                   {{ old('notify_new_episodes', $user->notify_new_episodes) ? 'checked' : '' }}
                   class="mt-1 w-5 h-5 rounded bg-white/5 border-white/20 text-rose-600 focus:ring-rose-500/50">
            <span>
                <span class="block text-sm font-bold text-white">{{ __('Notify me about new episodes') }}</span>
                <span class="block text-xs text-gray-400 mt-1">{{ __('Email when new episodes are released for a series you watch.') }}</span>
            </span>
        </label>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-900/40">
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
