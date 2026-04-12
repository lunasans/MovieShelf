@extends('cadmin.layout')

@section('header_title', 'Unterseiten')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tight">Unterseiten</h2>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Öffentlich erreichbar unter /p/{slug}</p>
        </div>
        <a href="{{ route('cadmin.landing.pages.create') }}"
           class="px-6 py-3 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-900/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            Neue Seite erstellen
        </a>
    </div>

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        @if($pages->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 px-8 text-center">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mb-6 border border-white/10">
                    <i class="bi bi-file-earmark-text text-gray-600 text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-white uppercase tracking-tight mb-2">Keine Unterseiten</h3>
                <p class="text-gray-500 max-w-sm font-medium">Erstelle Seiten wie Datenschutz, AGB oder eigene Inhaltsseiten.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] bg-white/[0.01]">
                            <th class="px-8 py-6 w-16">Pos</th>
                            <th class="px-8 py-6">Titel & Slug</th>
                            <th class="px-8 py-6">Navigation</th>
                            <th class="px-8 py-6">Sichtbarkeit</th>
                            <th class="px-8 py-6 text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($pages as $page)
                        <tr class="group hover:bg-white/[0.02] transition-colors">
                            <td class="px-8 py-6">
                                <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-xs font-black text-rose-500">
                                    {{ $page->sort_order }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-black text-white group-hover:text-rose-400 transition-colors">{{ $page->title }}</div>
                                <div class="text-[10px] text-gray-600 font-mono mt-1 flex items-center gap-2">
                                    <span>/p/{{ $page->slug }}</span>
                                    <a href="{{ route('landing.page', $page->slug) }}" target="_blank" class="text-gray-600 hover:text-blue-400 transition-colors">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @if($page->show_in_nav)
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-500/20">Im Footer</span>
                                @else
                                    <span class="text-[10px] text-gray-600 font-bold uppercase">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                @if($page->is_active)
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Aktiv</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 opacity-50">
                                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Inaktiv</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('cadmin.landing.pages.edit', $page) }}"
                                       class="px-4 py-2 bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white border border-white/10 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('cadmin.landing.pages.destroy', $page) }}" method="POST"
                                          onsubmit="return confirm('Seite wirklich löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-10 h-10 bg-white/5 hover:bg-rose-500/10 text-gray-500 hover:text-rose-500 border border-white/10 hover:border-rose-500/30 rounded-xl transition-all flex items-center justify-center">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
