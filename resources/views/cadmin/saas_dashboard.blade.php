@extends('cadmin.layout')

@section('header_title', 'System Dashboard')

@section('content')
<div class="space-y-12 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Welcome Header -->
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black text-white tracking-tight uppercase">Willkommen zurück</h2>
        <p class="text-gray-400 font-medium">Hier ist die Übersicht deiner MovieShelf Cloud Plattform.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Tenants -->
        <div class="glass rounded-[2rem] p-8 border border-white/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="bi bi-server text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-500 mb-4">Gesamt Filmregale</p>
                <h3 class="text-5xl font-black text-white tracking-tighter">{{ $stats['total_tenants'] }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-rose-500/80 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    Instanzen im System
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="glass rounded-[2rem] p-8 border border-white/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity text-emerald-500">
                <i class="bi bi-check-circle-fill text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-500 mb-4">Aktive Instanzen</p>
                <h3 class="text-5xl font-black text-emerald-400 tracking-tighter">{{ $stats['active_tenants'] }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-emerald-500/80 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Bereit & Online
                </div>
            </div>
        </div>

        <!-- Pending Tenants -->
        <div class="glass rounded-[2rem] p-8 border border-white/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity text-amber-500">
                <i class="bi bi-hourglass-split text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-500 mb-4">Warten auf Aktivierung</p>
                <h3 class="text-5xl font-black text-amber-400 tracking-tighter">{{ $stats['pending_tenants'] }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-amber-500/80 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Prüfung erforderlich
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        <div class="px-10 py-8 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 flex items-center justify-center border border-rose-500/20">
                    <i class="bi bi-clock-history text-rose-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Neueste Registrierungen</h3>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Die letzten 5 Anmeldungen</p>
                </div>
            </div>
            <a href="{{ route('cadmin.tenants') }}" class="px-6 py-2.5 bg-white/5 hover:bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all border border-white/10">
                Alle anzeigen <i class="bi bi-arrow-right ms-2 text-rose-500"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] bg-white/[0.01]">
                        <th class="px-10 py-6">ID / Subdomain</th>
                        <th class="px-10 py-6">E-Mail Adresse</th>
                        <th class="px-10 py-6">Status</th>
                        <th class="px-10 py-6 text-right">Registriert am</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recent_tenants as $tenant)
                    <tr class="group hover:bg-white/[0.02] transition-colors">
                        <td class="px-10 py-6 font-black text-white group-hover:text-rose-400 transition-colors">
                            {{ $tenant->id }}
                        </td>
                        <td class="px-10 py-6 text-gray-400 font-medium italic">
                            {{ $tenant->email ?? ($tenant->data['email'] ?? 'N/A') }}
                        </td>
                        <td class="px-10 py-6">
                            @if($tenant->activated_at)
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-500/20">
                                    Aktiviert
                                </span>
                            @else
                                <span class="px-3 py-1 bg-amber-500/10 text-amber-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-500/20">
                                    Ausstehend
                                </span>
                            @endif
                        </td>
                        <td class="px-10 py-6 text-right text-gray-500 font-bold text-sm">
                            {{ $tenant->created_at->format('d. F Y') }}
                            <div class="text-[10px] opacity-40">{{ $tenant->created_at->format('H:i') }} Uhr</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-20 text-center text-gray-500 font-bold uppercase tracking-widest">
                            Noch keine Registrierungen vorhanden.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection