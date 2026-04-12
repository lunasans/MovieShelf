@extends('cadmin.layout')

@section('header_title', 'FAQ Verwalten')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Header info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tight">Häufige Fragen</h2>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Verwalte die FAQs für die MovieShelf Landingpage</p>
        </div>
        <a href="{{ route('cadmin.faqs.create') }}" class="px-6 py-3 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-900/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            Neue FAQ erstellen
        </a>
    </div>

    <!-- FAQ Table/List -->
    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        @if($faqs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 px-8 text-center">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mb-6 border border-white/10">
                    <i class="bi bi-question-circle text-gray-600 text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-white uppercase tracking-tight mb-2">Keine FAQs gefunden</h3>
                <p class="text-gray-500 max-w-sm font-medium">Erstelle die erste FAQ, um sie für potenzielle Kunden auf der Startseite sichtbar zu machen.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] bg-white/[0.01]">
                            <th class="px-8 py-6 w-16">Pos</th>
                            <th class="px-8 py-6">Frage & Antwort</th>
                            <th class="px-8 py-6">Sichtbarkeit</th>
                            <th class="px-8 py-6 text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($faqs as $faq)
                        <tr class="group hover:bg-white/[0.02] transition-colors">
                            <td class="px-8 py-6">
                                <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-xs font-black text-rose-500">
                                    {{ $faq->sort_order }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-black text-white group-hover:text-rose-400 transition-colors mb-1">{{ $faq->question }}</div>
                                <div class="text-xs text-gray-500 italic max-w-xl line-clamp-2">{{ $faq->answer }}</div>
                            </td>
                            <td class="px-8 py-6">
                                @if($faq->is_active)
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
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cadmin.faqs.edit', $faq) }}" class="w-10 h-10 bg-white/5 hover:bg-blue-500/10 text-gray-500 hover:text-blue-400 border border-white/10 hover:border-blue-500/30 rounded-xl transition-all flex items-center justify-center" title="Bearbeiten">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('cadmin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Möchtest du diese FAQ wirklich löschen?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 bg-white/5 hover:bg-rose-500/10 text-gray-500 hover:text-rose-500 border border-white/10 hover:border-rose-500/30 rounded-xl transition-all flex items-center justify-center" title="Löschen">
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

