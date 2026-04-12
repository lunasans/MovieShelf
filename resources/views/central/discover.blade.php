@extends('layouts.central')

@section('title', 'Entdecken — Neue MovieShelf Regale')

@section('content')
<main class="relative z-10 pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        
        <div class="text-center mb-16 animate-reveal active">
            <span class="eyebrow">Entdecken</span>
            <h1 class="display mt-4 mb-6" style="font-size: clamp(2.5rem, 6vw, 4rem); line-height: 1.1;">
                Zuletzt eröffnete<br>
                <span class="display-italic">Regale.</span>
            </h1>
            <p class="text-gray-500 max-w-lg mx-auto leading-relaxed">
                Wirf einen Blick in die neuesten Sammlungen unserer Community. 
                Vielleicht findest du Inspiration für dein eigenes MovieShelf.
            </p>
        </div>

        <div class="grid gap-6 animate-reveal active delay-1">
            @forelse($tenants as $tenant)
                <div class="glass-ultra rounded-2xl p-6 md:p-8 flex items-center justify-between group hover:border-[#CC4B06]/30 transition-all duration-500">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 rounded-xl bg-orange-600/5 flex items-center justify-center text-[#CC4B06] group-hover:scale-110 transition-transform duration-500">
                            <i class="bi bi-collection-play text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-[#CC4B06] transition-colors">
                                {{ ucfirst($tenant->id) }}
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                Erstellt am {{ $tenant->activated_at->format('d.m.Y') }}
                            </p>
                        </div>
                    </div>
                    
                    <a href="https://{{ $tenant->id }}.movieshelf.info" target="_blank" 
                       class="inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-[#CC4B06] transition-all transform active:scale-95 group-hover:shadow-lg group-hover:shadow-orange-600/20">
                        Besuchen
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            @empty
                <div class="text-center py-20 bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-100">
                    <i class="bi bi-search text-4xl text-gray-200 mb-4 block"></i>
                    <p class="text-gray-400 font-medium">Noch keine Regale zum Entdecken vorhanden.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-20 text-center animate-reveal active delay-2">
            <div class="divider centered mb-8"></div>
            <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em] mb-8">Möchtest du dein eigenes Regal?</p>
            <a href="{{ route('landing') }}#subdomain" class="btn-primary" style="display: inline-flex; width: auto; padding-left: 3rem; padding-right: 3rem;">
                Jetzt kostenlos starten
            </a>
        </div>

    </div>
</main>
@endsection
