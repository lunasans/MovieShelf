@extends('admin.layout')

@section('content')
<div class="header">
    <h1>System Dashboard</h1>
    <div>
        <span style="color: var(--text-muted)">Willkommen zurück, Administrator</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 3rem;">
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Gesamt Filmregale</div>
        <div style="font-size: 2.5rem; font-weight: 700;">{{ $stats['total_tenants'] }}</div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Aktive Instanzen</div>
        <div style="font-size: 2.5rem; font-weight: 700; color: #10b981;">{{ $stats['active_tenants'] }}</div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Warten auf Aktivierung</div>
        <div style="font-size: 2.5rem; font-weight: 700; color: #f59e0b;">{{ $stats['pending_tenants'] }}</div>
    </div>
</div>

<div class="card">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.2rem;">Neueste Registrierungen</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; border-bottom: 1px solid #333;">
                <th style="padding: 1rem;">ID / Subdomain</th>
                <th style="padding: 1rem;">E-Mail</th>
                <th style="padding: 1rem;">Status</th>
                <th style="padding: 1rem;">Datum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_tenants as $tenant)
            <tr style="border-bottom: 1px solid #222;">
                <td style="padding: 1rem; font-weight: 600;">{{ $tenant->id }}</td>
                <td style="padding: 1rem; color: var(--text-muted);">{{ $tenant->data['email'] ?? 'N/A' }}</td>
                <td style="padding: 1rem;">
                    @if($tenant->activated_at)
                        <span style="color: #10b981; font-size: 0.85rem;">● Aktiviert</span>
                    @else
                        <span style="color: #f59e0b; font-size: 0.85rem;">○ Ausstehend</span>
                    @endif
                </td>
                <td style="padding: 1rem; color: var(--text-muted); font-size: 0.85rem;">{{ $tenant->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 1.5rem; text-align: right;">
        <a href="{{ route('admin.tenants') }}" style="color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Alle anzeigen →</a>
    </div>
</div>
@endsection