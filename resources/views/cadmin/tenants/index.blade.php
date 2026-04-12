@extends('cadmin.layout')

@section('header_title', 'Filmregale (Tenants)')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Header info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tight">Alle Instanzen</h2>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Verwaltung der registrierten MovieShelf Web-Services</p>
        </div>
        @php
            $modeBadge = match($onboardingMode) {
                'auto'  => ['bg' => 'bg-green-500/10',  'border' => 'border-green-500/20',  'icon' => 'bi-lightning-charge-fill text-green-500',  'text' => 'text-green-400',  'label' => 'Sofort-Aktivierung aktiv'],
                'email' => ['bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/20',   'icon' => 'bi-envelope-fill text-blue-500',            'text' => 'text-blue-400',   'label' => 'E-Mail Aktivierung aktiv'],
                default => ['bg' => 'bg-rose-500/10',   'border' => 'border-rose-500/20',   'icon' => 'bi-shield-lock-fill text-rose-500',         'text' => 'text-rose-400',   'label' => 'Manuelle Aktivierung erforderlich'],
            };
        @endphp
        <div class="flex items-center gap-2 px-4 py-2 {{ $modeBadge['bg'] }} border {{ $modeBadge['border'] }} rounded-xl">
            <i class="bi {{ $modeBadge['icon'] }}"></i>
            <span class="text-[10px] font-black {{ $modeBadge['text'] }} uppercase tracking-widest">{{ $modeBadge['label'] }}</span>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] bg-white/[0.01]">
                        <th class="px-8 py-6">ID / Subdomain</th>
                        <th class="px-8 py-6">Admin Kontakt</th>
                        <th class="px-8 py-6">Domains</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tenants as $tenant)
                    <tr class="group hover:bg-white/[0.02] transition-colors">
                        <td class="px-8 py-6">
                            <div class="font-black text-white group-hover:text-rose-400 transition-colors">{{ $tenant->id }}</div>
                            <div class="text-[10px] text-gray-600 font-bold uppercase tracking-widest mt-0.5">Seit {{ $tenant->created_at->format('d.m.Y') }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-bold text-gray-300">{{ $tenant->email ?? ($tenant->data['email'] ?? 'System') }}</div>
                            <div class="text-[10px] text-gray-600 font-medium uppercase tracking-tighter">Tenant-Owner</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                @foreach($tenant->domains as $domain)
                                    <span class="text-xs font-mono text-rose-300/60 transition-colors group-hover:text-rose-400">
                                        {{ $domain->domain }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @if($tenant->activated_at)
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Aktiv</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shadow-[0_0_10px_rgba(245,158,11,0.5)]"></span>
                                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em]">Wartend</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-end gap-3">
                                @if(!$tenant->activated_at)
                                <form action="{{ route('cadmin.tenants.activate', $tenant) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-500 hover:text-white border border-emerald-500/20 rounded-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                                        <i class="bi bi-person-check-fill"></i>
                                        Aktivieren
                                    </button>
                                </form>
                                @endif
                                
                                <form action="{{ route('cadmin.tenants.delete', $tenant) }}" method="POST" onsubmit="return confirm('Soll dieses Filmregal wirklich unwiderruflich gelöscht werden?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 bg-white/5 hover:bg-rose-500/10 text-gray-500 hover:text-rose-500 border border-white/10 hover:border-rose-500/30 rounded-xl transition-all flex items-center justify-center" title="Instanz löschen">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-gray-500 font-bold uppercase tracking-widest">
                            Noch keine Filmregale in der Datenbank.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tenants->hasPages())
        <div class="px-8 py-6 bg-white/[0.01] border-t border-white/5">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

