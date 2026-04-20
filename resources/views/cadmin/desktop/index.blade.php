@extends('cadmin.layout')

@section('header_title', 'Desktop App Releases')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl font-black text-white tracking-tight uppercase">App Releases</h2>
            <p class="text-gray-400 font-medium">Verwalte die Versionen und Downloads für die MovieShelf Desktop-App.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('releases') }}" target="_blank"
               class="px-6 py-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-black uppercase tracking-widest text-xs rounded-2xl transition-all flex items-center gap-2">
                <i class="bi bi-box-arrow-up-right"></i> Archiv ansehen
            </a>
            <a href="{{ route('cadmin.desktop.create') }}"
               class="px-8 py-4 bg-rose-600 hover:bg-rose-500 text-white font-black uppercase tracking-widest text-xs rounded-2xl shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
                <i class="bi bi-plus-lg"></i> Neues Release
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl px-6 py-4 text-sm text-emerald-400 font-bold">
        <i class="bi bi-check-circle-fill mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="space-y-4" x-data="{ open: null }">
        @forelse($releases as $release)
        <div class="glass rounded-[2rem] border border-white/10 overflow-hidden shadow-xl transition-all"
             :class="open === {{ $release->id }} ? 'border-rose-500/20' : ''">

            {{-- Main row --}}
            <div class="flex items-center gap-4 px-8 py-5 cursor-pointer select-none"
                 @click="open = open === {{ $release->id }} ? null : {{ $release->id }}">

                {{-- Version + date --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-white font-black text-lg">{{ $release->version }}</span>
                        @if($release->is_public)
                            <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-black uppercase tracking-widest rounded-full border border-emerald-500/20">Öffentlich</span>
                        @else
                            <span class="px-2.5 py-0.5 bg-white/5 text-gray-500 text-[9px] font-black uppercase tracking-widest rounded-full border border-white/5">Entwurf</span>
                        @endif
                    </div>
                    <span class="text-[10px] text-gray-600 font-semibold mt-0.5 block">
                        {{ $release->created_at->format('d.m.Y') }}
                    </span>
                </div>

                {{-- Platform badges --}}
                <div class="hidden md:flex items-center gap-2 shrink-0">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest {{ $release->download_url ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-white/5 text-gray-700 border border-white/5' }}">
                        <i class="bi bi-windows"></i> Win
                        @if($release->file_hash) <i class="bi bi-shield-check text-emerald-500" title="SHA-256"></i> @endif
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest {{ $release->download_url_linux_appimage ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : 'bg-white/5 text-gray-700 border border-white/5' }}">
                        <i class="bi bi-ubuntu"></i> AppImage
                        @if($release->file_hash_linux_appimage) <i class="bi bi-shield-check text-emerald-500" title="SHA-256"></i> @endif
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest {{ $release->download_url_linux_deb ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-white/5 text-gray-700 border border-white/5' }}">
                        <i class="bi bi-terminal"></i> deb
                        @if($release->file_hash_linux_deb) <i class="bi bi-shield-check text-emerald-500" title="SHA-256"></i> @endif
                    </span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('cadmin.desktop.edit', $release) }}"
                       class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all border border-white/10"
                       @click.stop>
                        <i class="bi bi-pencil-fill text-xs"></i>
                    </a>
                    <form action="{{ route('cadmin.desktop.destroy', $release) }}" method="POST"
                          onsubmit="return confirm('Release {{ $release->version }} wirklich löschen?')" @click.stop>
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 transition-all border border-rose-500/20">
                            <i class="bi bi-trash3-fill text-xs"></i>
                        </button>
                    </form>
                    <div class="w-6 flex items-center justify-center text-gray-600 transition-transform duration-200"
                         :class="open === {{ $release->id }} ? 'rotate-180' : ''">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

            </div>

            {{-- Expandable detail --}}
            <div x-show="open === {{ $release->id }}" x-collapse x-cloak>
                <div class="border-t border-white/5 px-8 py-6 space-y-5">

                    {{-- Download links --}}
                    <div class="space-y-2">
                        <p class="text-[9px] font-black text-white/20 uppercase tracking-[.3em]">Download-Links</p>
                        <div class="space-y-1.5">
                            @foreach([
                                ['label'=>'Windows', 'icon'=>'bi-windows', 'url'=>$release->download_url, 'hash'=>$release->file_hash],
                                ['label'=>'AppImage', 'icon'=>'bi-ubuntu', 'url'=>$release->download_url_linux_appimage, 'hash'=>$release->file_hash_linux_appimage],
                                ['label'=>'Debian',   'icon'=>'bi-terminal', 'url'=>$release->download_url_linux_deb, 'hash'=>$release->file_hash_linux_deb],
                            ] as $dl)
                            <div class="flex items-center gap-3 text-xs">
                                <span class="w-20 text-gray-600 font-bold uppercase tracking-widest text-[9px] flex items-center gap-1.5">
                                    <i class="bi {{ $dl['icon'] }}"></i> {{ $dl['label'] }}
                                </span>
                                @if($dl['url'])
                                    <a href="{{ $dl['url'] }}" target="_blank" class="text-rose-400 hover:underline truncate max-w-xs font-mono text-[10px]">{{ $dl['url'] }}</a>
                                    @if($dl['hash'])
                                    <span class="text-[9px] font-mono text-gray-600 flex items-center gap-1 shrink-0">
                                        <i class="bi bi-shield-check text-emerald-500"></i>
                                        {{ substr($dl['hash'], 0, 12) }}…
                                    </span>
                                    @endif
                                @else
                                    <span class="text-gray-700 italic">nicht hinterlegt</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Changelog --}}
                    @if($release->changelog)
                    <div class="space-y-2">
                        <p class="text-[9px] font-black text-white/20 uppercase tracking-[.3em]">Changelog</p>
                        <div class="text-sm text-gray-400 leading-relaxed whitespace-pre-line bg-white/[.02] rounded-2xl px-5 py-4 border border-white/5">{{ $release->changelog }}</div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
        @empty
        <div class="glass rounded-[2rem] border border-white/10 px-10 py-20 text-center text-gray-500 font-bold uppercase tracking-widest">
            Noch keine Desktop-Releases vorhanden.
        </div>
        @endforelse
    </div>

</div>
@endsection
