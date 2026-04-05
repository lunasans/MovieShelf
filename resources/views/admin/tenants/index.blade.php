@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Alle Filmregale</h1>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; border-bottom: 1px solid #333;">
                <th style="padding: 1rem;">ID</th>
                <th style="padding: 1rem;">Admin E-Mail</th>
                <th style="padding: 1rem;">Domains</th>
                <th style="padding: 1rem;">Status</th>
                <th style="padding: 1rem; text-align: right;">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenants as $tenant)
            <tr style="border-bottom: 1px solid #222;">
                <td style="padding: 1rem;">
                    <div style="font-weight: 600;">{{ $tenant->id }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Erstellt: {{ $tenant->created_at->format('d.m.y') }}</div>
                </td>
                <td style="padding: 1rem; color: var(--text-muted);">
                    {{ $tenant->email ?? ($tenant->data['email'] ?? 'System') }}
                </td>
                <td style="padding: 1rem;">
                    @foreach($tenant->domains as $domain)
                        <div style="font-size: 0.85rem;">{{ $domain->domain }}</div>
                    @endforeach
                </td>
                <td style="padding: 1rem;">
                    @if($tenant->activated_at)
                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">AKTIV</span>
                    @else
                        <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">WARTEND</span>
                    @endif
                </td>
                <td style="padding: 1rem; text-align: right;">
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        @if(!$tenant->activated_at)
                        <form action="{{ url('admin/tenants/'.$tenant->id.'/activate') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: #10b981;">Aktivieren</button>
                        </form>
                        @endif
                        
                        <form action="{{ url('admin/tenants/'.$tenant->id.'/delete') }}" method="POST" onsubmit="return confirm('Soll dieses Filmregal wirklich unwiderruflich gelöscht werden?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: #333;">Löschen</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 2rem;">
        {{ $tenants->links() }}
    </div>
</div>
@endsection
