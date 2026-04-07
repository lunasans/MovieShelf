@extends('admin.layout')

@section('content')
<div class="header">
    <h1>FAQ Verwalten</h1>
    <a href="{{ route('admin.faqs.create') }}" class="btn">
        <i class="bi bi-plus-lg"></i> Neue FAQ erstellen
    </a>
</div>

<div class="card">
    @if($faqs->isEmpty())
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="bi bi-question-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
            Noch keine FAQs vorhanden. Erstelle die erste FAQ, um sie auf der Landingpage anzuzeigen.
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; border-bottom: 1px solid #333;">
                    <th style="padding: 1rem;">#</th>
                    <th style="padding: 1rem;">Frage</th>
                    <th style="padding: 1rem;">Status</th>
                    <th style="padding: 1rem; text-align: right;">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($faqs as $faq)
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 1rem; color: var(--text-muted);">{{ $faq->sort_order }}</td>
                    <td style="padding: 1rem;">
                        <div style="font-weight: 600;">{{ $faq->question }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                            {{ Str::limit($faq->answer, 100) }}
                        </div>
                    </td>
                    <td style="padding: 1rem;">
                        @if($faq->is_active)
                            <span style="color: #10b981; font-size: 0.85rem;">● Aktiv</span>
                        @else
                            <span style="color: #f59e0b; font-size: 0.85rem;">○ Inaktiv</span>
                        @endif
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" style="color: #3b82f6; text-decoration: none; padding: 0.5rem;" title="Bearbeiten">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Möchtest du diese FAQ wirklich löschen?')" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.5rem;" title="Löschen">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
