@props(['block' => false])

@php
    $locales = config('app.supported_locales', []);
    $current = app()->getLocale();
    $currentLabel = $locales[$current] ?? strtoupper($current);
@endphp

@if(count($locales) > 1)
    @if($block)
        {{-- Mobiles Menue: volle Breite, ausgeschriebene Sprachnamen --}}
        <div class="flex flex-col gap-1">
            @foreach($locales as $code => $label)
                @if($code === $current)
                    <span aria-current="true"
                          class="flex items-center justify-between px-4 py-2 rounded-lg bg-white/10 text-sm font-bold text-white">
                        {{ $label }}
                        <i class="bi bi-check2 text-rose-500"></i>
                    </span>
                @else
                    <a href="{{ route('locale.switch', $code) }}"
                       aria-label="{{ __('Switch language to :language', ['language' => $label]) }}"
                       class="px-4 py-2 rounded-lg text-sm font-bold text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </div>
    @else
        {{-- Desktop-Navigation: ein Knopf mit Globus und aktueller Sprache.
             Zwei nebeneinander gesetzte Kuerzel verschmelzen dort optisch zu
             einem Wort ("ENDE"), deshalb liegt die zweite Sprache im Menue. --}}
        <div class="relative" x-data="{ langOpen: false }" @click.outside="langOpen = false">
            <button type="button" @click="langOpen = !langOpen"
                    :aria-expanded="langOpen ? 'true' : 'false'"
                    aria-haspopup="true"
                    aria-label="{{ __('Change language') }}"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-white/10 bg-white/[0.04] text-gray-400 hover:text-white hover:border-white/20 transition-colors">
                <i class="bi bi-globe2 text-[13px]"></i>
                <span class="text-[11px] font-black uppercase tracking-wider">{{ $current }}</span>
                <i class="bi bi-chevron-down text-[9px] opacity-60"></i>
            </button>

            <div x-show="langOpen" x-cloak
                 class="absolute right-0 mt-2 min-w-[10rem] rounded-xl border border-white/10 bg-[#0d0f13] shadow-2xl p-1 z-50">
                @foreach($locales as $code => $label)
                    @if($code === $current)
                        <span aria-current="true"
                              class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-white/10 text-[13px] font-bold text-white">
                            {{ $label }}
                            <i class="bi bi-check2 text-rose-500"></i>
                        </span>
                    @else
                        <a href="{{ route('locale.switch', $code) }}"
                           aria-label="{{ __('Switch language to :language', ['language' => $label]) }}"
                           class="block px-3 py-2 rounded-lg text-[13px] font-bold text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                            {{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
@endif
