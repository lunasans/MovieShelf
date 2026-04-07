@extends('layouts.saas')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --accent: #CC4B06;
        --text: #1A1A1A;
        --muted: #6B7280;
        --border: #E5E7EB;
        --surface: #F9F9F7;
        --white: #FFFFFF;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--white);
        color: var(--text);
        line-height: 1.6;
    }

    .legal-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 8rem 2rem;
    }

    .legal-content h1 {
        font-family: 'Fraunces', serif;
        font-size: 3rem;
        margin-bottom: 2rem;
        font-weight: 700;
    }

    .legal-content h2 {
        font-family: 'Fraunces', serif;
        font-size: 1.5rem;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .legal-content p {
        margin-bottom: 1.25rem;
        color: var(--muted);
    }

    .legal-content a {
        color: var(--accent);
        text-decoration: underline;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--muted);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 3rem;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--accent);
    }
</style>

<div class="legal-container">
    <a href="{{ route('landing') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Zurück zur Startseite
    </a>

    <div class="legal-content">
        @if(\App\Models\Setting::get('saas_impressum_active', '0') == '1')
            {!! \App\Models\Setting::get('saas_impressum_content', 'No content available.') !!}
        @else
            <h1>Impressum</h1>
            <p>Das Impressum ist derzeit nicht öffentlich verfügbar.</p>
        @endif
    </div>
</div>

@endsection
