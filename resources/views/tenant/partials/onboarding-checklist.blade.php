@isset($onboarding)
@if($onboarding && ! $onboarding['dismissed'] && ! $onboarding['complete'])
<div class="mb-6 mx-2 rounded-3xl border border-white/15 bg-white/5 backdrop-blur-2xl p-6 shadow-2xl">
    <div class="flex items-start justify-between gap-4 mb-5">
        <div>
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <i class="bi bi-rocket-takeoff-fill text-rose-400"></i>
                Erste Schritte
            </h2>
            <p class="text-sm text-gray-400 mt-1">Richte dein Regal in wenigen Schritten ein.</p>
        </div>
        <form method="POST" action="{{ route('onboarding.dismiss') }}">
            @csrf
            <button type="submit" title="Checkliste ausblenden"
                    class="text-gray-500 hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-white/10 transition-colors whitespace-nowrap">
                Ausblenden
            </button>
        </form>
    </div>

    {{-- Fortschritt --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex-1 h-2 rounded-full bg-white/10 overflow-hidden">
            <div class="h-full bg-rose-500 rounded-full" style="width: {{ $onboarding['percent'] }}%"></div>
        </div>
        <span class="text-xs font-black text-gray-400 whitespace-nowrap">{{ $onboarding['done'] }} / {{ $onboarding['total'] }}</span>
    </div>

    <ul class="space-y-3">
        @foreach($onboarding['steps'] as $step)
        <li class="flex items-center gap-4 p-4 rounded-2xl border {{ $step['done'] ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-white/10 bg-white/5' }}">
            <span class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center {{ $step['done'] ? 'bg-emerald-500 text-white' : 'bg-white/10 text-gray-300' }}">
                <i class="bi {{ $step['done'] ? 'bi-check-lg' : $step['icon'] }}"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-white text-sm {{ $step['done'] ? 'line-through opacity-60' : '' }}">{{ $step['label'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $step['desc'] }}</p>
            </div>
            @unless($step['done'])
            <a href="{{ $step['url'] }}"
               class="shrink-0 text-[11px] font-black uppercase tracking-wide px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/30 text-rose-300 transition-colors whitespace-nowrap">
                {{ $step['cta'] }}
            </a>
            @endunless
        </li>
        @endforeach
    </ul>
</div>
@endif
@endisset
