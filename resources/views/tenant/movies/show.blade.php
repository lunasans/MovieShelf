<x-app-layout>
@if($layoutMode === 'streaming')
    @include('tenant.movies.partials.streaming-details')
@else
    <div class="relative min-h-screen">
        <!-- Backdrop (Subtle) -->
        <div class="absolute inset-0 h-[50vh] bg-gradient-to-b from-blue-600/5 to-transparent opacity-20 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-8 py-20 relative z-10">
            <!-- Back Button -->
            <div class="mb-10">
                <a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-3 px-6 py-2.5 glass hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-sm font-bold text-gray-400 hover:text-white">
                    <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
                    {{ __('Zurück zur Übersicht') }}
                </a>
            </div>

            <div class="glass-strong p-10 rounded-[3.5rem] border border-white/10 shadow-[0_0_100px_rgba(0,0,0,0.5)]">
                @include('tenant.movies.partials.details', ['movie' => $movie])
            </div>
        </div>
    </div>
@endif
</x-app-layout>
