@extends('cadmin.layout')

@section('header_title', 'Desktop App Releases')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl font-black text-white tracking-tight uppercase">App Releases</h2>
            <p class="text-gray-400 font-medium">Verwalte die Versionen und Downloads für die MovieShelf Desktop-App.</p>
        </div>
        <a href="{{ route('cadmin.desktop.create') }}" class="px-8 py-4 bg-rose-600 hover:bg-rose-500 text-white font-black uppercase tracking-widest text-xs rounded-2xl shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Neues Release
        </a>
    </div>

    <!-- Table -->
    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] bg-white/[0.01]">
                        <th class="px-10 py-6">Version</th>
                        <th class="px-10 py-6">Status</th>
                        <th class="px-10 py-6">Download-Link / File</th>
                        <th class="px-10 py-6 text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($releases as $release)
                    <tr class="group hover:bg-white/[0.02] transition-colors">
                        <td class="px-10 py-6 font-black text-white group-hover:text-rose-400 transition-colors">
                            {{ $release->version }}
                        </td>
                        <td class="px-10 py-6">
                            @if($release->is_public)
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-500/20">
                                    Öffentlich
                                </span>
                            @else
                                <span class="px-3 py-1 bg-white/5 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-white/5">
                                    Entwurf
                                </span>
                            @endif
                        </td>
                        <td class="px-10 py-6">
                            @if($release->download_url)
                                <a href="{{ $release->download_url }}" target="_blank" class="text-xs text-rose-400 hover:underline font-bold flex items-center gap-2">
                                    <i class="bi bi-link-45deg"></i> Download aufrufen
                                </a>
                            @else
                                <span class="text-xs text-gray-500 italic">Kein Link hinterlegt</span>
                            @endif
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('cadmin.desktop.edit', $release) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all border border-white/10">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('cadmin.desktop.destroy', $release) }}" method="POST" onsubmit="return confirm('Release wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 transition-all border border-rose-500/20">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-20 text-center text-gray-500 font-bold uppercase tracking-widest">
                            Noch keine Desktop-Releases vorhanden.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
