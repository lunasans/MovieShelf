<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill text-rose-500"></i>
            {{ __('Delete shelf & account') }}
        </h2>

        <p class="mt-2 text-sm text-gray-400 font-medium">
            {{ __('Once your shelf is deleted, all data (movies, covers, settings) is removed permanently.') }}
            <br>
            <span class="text-rose-400">{{ __('After clicking we will send you a confirmation email containing a deletion link.') }}</span>
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Request shelf deletion') }}</x-danger-button>

    @push('modals')
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-black text-white tracking-tight mb-2">
                {{ __('Really delete this shelf?') }}
            </h2>

            <p class="text-sm text-gray-400 font-medium">
                {{ __('Please enter your password to start the deletion request by email.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password for confirmation') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Request deletion link') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @endpush
</section>
