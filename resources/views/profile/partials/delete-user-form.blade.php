<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill text-rose-500"></i>
            {{ __('Shelf & Account löschen') }}
        </h2>

        <p class="mt-2 text-sm text-gray-400 font-medium">
            {{ __('Sobald dein Shelf gelöscht wurde, werden alle Daten (Filme, Cover, Einstellungen) unwiderruflich entfernt.') }}
            <br>
            <span class="text-rose-400">{{ __('Nach dem Klick senden wir dir eine Bestätigungs-E-Mail mit einem Löschlink zu.') }}</span>
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Shelf-Löschung anfordern') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-black text-white tracking-tight mb-2">
                {{ __('Shelf wirklich löschen?') }}
            </h2>

            <p class="text-sm text-gray-400 font-medium">
                {{ __('Bitte gib dein Passwort ein, um die Löschanfrage per E-Mail zu starten.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Passwort zur Bestätigung') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Abbrechen') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Löschlink anfordern') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
