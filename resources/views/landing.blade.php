@extends('layouts.saas')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --accent: #CC4B06;
        --accent-hover: #A33C05;
        --text: #1A1A1A;
        --muted: #6B7280;
        --border: #E5E7EB;
        --surface: #F9F9F7;
        --white: #FFFFFF;
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--white);
        color: var(--text);
        -webkit-font-smoothing: antialiased;
    }

    /* ─── Typography ─────────────────────────────────────── */
    .display {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .display-italic {
        font-family: 'Fraunces', Georgia, serif;
        font-style: italic;
        font-weight: 400;
    }

    /* ─── Layout helpers ─────────────────────────────────── */
    .container {
        max-width: 1120px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .section { padding: 6rem 0; }
    .section-sm { padding: 4rem 0; }

    /* ─── Animations ─────────────────────────────────────── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up   { animation: fadeUp 0.6s ease both; }
    .delay-1   { animation-delay: 0.1s; }
    .delay-2   { animation-delay: 0.22s; }
    .delay-3   { animation-delay: 0.36s; }
    .delay-4   { animation-delay: 0.5s; }

    /* ─── Eyebrow label ──────────────────────────────────── */
    .eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--accent);
    }

    /* ─── Hero ───────────────────────────────────────────── */
    .hero {
        padding: 9rem 0 6rem;
        text-align: center;
        border-bottom: 1px solid var(--border);
    }

    .hero h1 {
        font-size: clamp(3rem, 7vw, 5.5rem);
        margin: 0.5rem 0 1.5rem;
        color: var(--text);
    }

    .hero p {
        font-size: 1.125rem;
        color: var(--muted);
        max-width: 480px;
        margin: 0 auto 3.5rem;
        line-height: 1.7;
    }

    /* ─── Subdomain input ────────────────────────────────── */
    .subdomain-wrap {
        max-width: 640px;
        margin: 0 auto;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: var(--white);
        display: flex;
        align-items: center;
        padding: 0 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        gap: 0;
    }

    .subdomain-wrap:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(204, 75, 6, 0.08);
    }

    .subdomain-wrap .prefix,
    .subdomain-wrap .suffix {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--muted);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .subdomain-wrap input {
        flex: 1;
        border: none;
        outline: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        background: transparent;
        padding: 1rem 0.5rem;
        min-width: 0;
    }

    .subdomain-wrap input::placeholder {
        color: #D1D5DB;
        font-weight: 400;
    }

    .status-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }
    .status-available { background: #10B981; }
    .status-taken     { background: #EF4444; }
    .status-checking  {
        border: 2px solid var(--accent);
        border-top-color: transparent;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ─── Registration form ──────────────────────────────── */
    .reg-form {
        margin-top: 2.5rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2rem;
        max-width: 640px;
        margin-left: auto;
        margin-right: auto;
        text-align: left;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 540px) {
        .form-grid { grid-template-columns: 1fr; }
    }

    .field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 0.4rem;
    }

    .field input {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.65rem 0.9rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.95rem;
        background: var(--white);
        color: var(--text);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .field input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(204, 75, 6, 0.08);
    }

    .btn-primary {
        display: block;
        width: 100%;
        margin-top: 1.25rem;
        padding: 0.9rem 1.5rem;
        background: var(--accent);
        color: var(--white);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-primary:hover  { background: var(--accent-hover); transform: translateY(-1px); }
    .btn-primary:active { transform: translateY(0); }

    /* ─── Screenshot section ─────────────────────────────── */
    .screenshot-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: var(--surface);
    }

    .screenshot-wrap img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* ─── Feature grid ───────────────────────────────────── */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .feature-item {
        padding: 1.5rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--white);
    }

    .feature-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(204, 75, 6, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .feature-item h3 {
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0 0 0.35rem;
        color: var(--text);
    }

    .feature-item p {
        font-size: 0.85rem;
        color: var(--muted);
        margin: 0;
        line-height: 1.6;
    }

    /* ─── Comparison cards ───────────────────────────────── */
    .compare-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        max-width: 760px;
        margin: 0 auto;
    }

    @media (max-width: 600px) {
        .compare-grid { grid-template-columns: 1fr; }
    }

    .plan-card {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2rem;
        background: var(--white);
        position: relative;
    }

    .plan-card.featured {
        border-color: var(--accent);
        border-width: 2px;
    }

    .plan-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--accent);
        color: var(--white);
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 3px 12px;
        border-radius: 20px;
    }

    .plan-card h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0 0 0.4rem;
        color: var(--text);
    }

    .plan-card p.sub {
        font-size: 0.85rem;
        color: var(--muted);
        margin: 0 0 1.5rem;
        line-height: 1.5;
    }

    .plan-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .plan-list li {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .plan-list li.check { color: var(--text); }
    .plan-list li.dim   { color: #CBD5E1; }

    .check-icon {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
    }

    li.check .check-icon { background: #1A1A1A; color: #FFF; }
    li.dim   .check-icon { background: #E5E7EB; color: #E5E7EB; }

    .btn-outline {
        display: block;
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
        background: transparent;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-outline:hover { background: var(--surface); }

    .btn-dark {
        display: block;
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--white);
        background: var(--text);
        text-align: center;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .btn-dark:hover { opacity: 0.85; }

    /* ─── Beta section ───────────────────────────────────── */
    .beta-section {
        background: var(--surface);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }

    .beta-inner {
        display: flex;
        align-items: center;
        gap: 3rem;
    }

    @media (max-width: 700px) {
        .beta-inner { flex-direction: column; text-align: center; gap: 1.5rem; }
    }

    .beta-inner .text { flex: 1; }
    .beta-inner h2 { font-size: 1.5rem; margin: 0 0 0.4rem; }
    .beta-inner p  { font-size: 0.9rem; color: var(--muted); margin: 0; line-height: 1.6; }

    .beta-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-shrink: 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pulse-dot {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #D97706;
        margin-bottom: 0.5rem;
    }

    .pulse-dot span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #F59E0B;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(1.3); }
    }

    /* ─── Divider ────────────────────────────────────────── */
    .divider {
        width: 48px;
        height: 3px;
        background: var(--accent);
        border-radius: 2px;
        margin: 1rem 0 0;
    }
    .divider.centered { margin: 1rem auto 0; }

    /* ─── CTA section ────────────────────────────────────── */
    .cta-section {
        text-align: center;
        padding: 7rem 0;
    }

    .cta-section h2 {
        font-size: clamp(2rem, 5vw, 3.5rem);
        margin: 0 0 2rem;
    }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.9rem 2rem;
        background: var(--accent);
        color: var(--white);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-cta:hover { background: var(--accent-hover); transform: translateY(-1px); }

    .footer-note {
        margin-top: 3rem;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #CBD5E1;
    }

    /* ─── FAQ Accordion ──────────────────────────────────── */
    .faq-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-item {
        border-bottom: 1px solid var(--border);
        padding: 1.5rem 0;
    }

    .faq-question {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        text-align: left;
        color: var(--text);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 600;
        transition: color 0.2s;
    }

    .faq-question:hover { color: var(--accent); }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, margin-top 0.3s;
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.7;
    }

    .faq-answer a { color: var(--accent); text-decoration: underline; }
    .faq-answer ul, .faq-answer ol { padding-left: 1.5rem; margin: 1rem 0; }
    .faq-answer li { margin-bottom: 0.5rem; }

    .faq-item.active .faq-answer {
        max-height: 500px;
        margin-top: 1rem;
    }

    .faq-icon {
        width: 20px;
        height: 20px;
        position: relative;
        flex-shrink: 0;
        margin-left: 1rem;
    }

    .faq-icon::before, .faq-icon::after {
        content: '';
        position: absolute;
        background: currentColor;
        transition: transform 0.3s;
        top: 50%;
        left: 50%;
    }

    .faq-icon::before { width: 14px; height: 2px; margin-left: -7px; margin-top: -1px; }
    .faq-icon::after  { width: 2px; height: 14px; margin-left: -1px; margin-top: -7px; }

    .faq-item.active .faq-icon::after { transform: rotate(90deg); opacity: 0; }
    .faq-item.active .faq-icon::before { transform: rotate(180deg); }
</style>


{{-- ═══════════════════════════════════════════════════════ HERO ═══════════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="container">

        <span class="eyebrow fade-up">MovieShelf Cloud</span>

        <h1 class="display fade-up delay-1">
            Dein digitales<br>
            <span class="display-italic">Filmregal.</span>
        </h1>

        <p class="fade-up delay-2">
            Organisiere, verwalte und teile deine Filmsammlung –
            modern, schnell und ohne technischen Aufwand.
        </p>

        {{-- ── Subdomain Form ────────────────────────────── --}}
        <div x-data="{
            subdomain: '{{ old('subdomain') }}',
            available: {{ old('subdomain') ? 'true' : 'null' }},
            checking: false,
            async checkAvailability() {
                if (this.subdomain.length < 3) { this.available = null; return; }
                this.checking = true;
                try {
                    const res  = await fetch('{{ route('api.check.subdomain') }}?name=' + this.subdomain);
                    const data = await res.json();
                    this.available = data.available;
                    this.subdomain = data.slug;
                    this.statusMessage = data.message || '';
                } catch(e) {
                    this.available = null;
                } finally {
                    this.checking = false;
                }
            },
            statusMessage: '',
            acceptedTerms: false
        }" class="fade-up delay-3">

            <form action="{{ route('tenant.register') }}" method="POST">
                @csrf

                {{-- URL input row --}}
                <div class="subdomain-wrap">
                    <span class="prefix">https://</span>
                    <input
                        type="text"
                        id="subdomain"
                        name="subdomain"
                        x-model="subdomain"
                        @input.debounce.900ms="checkAvailability()"
                        placeholder="dein-name"
                        required
                        autocomplete="off"
                    >
                    <span class="suffix">.movieshelf.info</span>
                    <template x-if="checking">
                        <div class="status-icon status-checking" style="margin-left:.75rem"></div>
                    </template>
                    <template x-if="!checking && available === true">
                        <div class="status-icon status-available" style="margin-left:.75rem">
                            <i class="bi bi-check" style="color:#fff;font-size:12px;line-height:20px;width:20px;text-align:center;display:block"></i>
                        </div>
                    </template>
                    <template x-if="!checking && available === false">
                        <div class="status-icon status-taken" style="margin-left:.75rem">
                            <i class="bi bi-x" style="color:#fff;font-size:12px;line-height:20px;width:20px;text-align:center;display:block"></i>
                        </div>
                    </template>
                </div>

                <template x-if="!checking && available === false && statusMessage">
                    <p style="color: #e50914; font-size: 0.75rem; margin-top: 0.5rem; font-weight: 600; text-align: center;">
                        <i class="bi bi-exclamation-circle"></i> <span x-text="statusMessage"></span>
                    </p>
                </template>

                {{-- Registration fields (shown when subdomain is available) --}}
                <div
                    x-show="available === true"
                    x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="reg-form"
                >
                    <div class="form-grid">
                        <div class="field">
                            <label>Vor- & Nachname</label>
                            <input type="text" name="name" placeholder="Max Mustermann" required>
                        </div>
                        <div class="field">
                            <label>E-Mail</label>
                            <input type="email" name="email" placeholder="max@example.com" required>
                        </div>
                        <div class="field">
                            <label>Passwort</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div style="margin: 1.5rem 0; display: flex; align-items: flex-start; gap: 0.75rem; text-align: left;">
                        <input type="checkbox" name="terms" id="terms" x-model="acceptedTerms" required
                               style="width: 1.25rem; height: 1.25rem; margin-top: 0.2rem; cursor: pointer; accent-color: var(--accent);">
                        <label for="terms" style="font-size: 0.85rem; color: var(--muted); cursor: pointer; line-height: 1.5;">
                            Ich akzeptiere die <a href="/privacy" target="_blank" style="color: var(--accent); text-decoration: underline;">Nutzungsbedingungen</a> und habe die Datenschutzbelehrung gelesen.
                        </label>
                    </div>

                    <button type="submit" class="btn-primary" :disabled="!acceptedTerms" :style="!acceptedTerms ? 'opacity: 0.5; cursor: not-allowed; filter: grayscale(1);' : ''">
                        Cloud einrichten →
                    </button>
                </div>

            </form>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ SCREENSHOT ═══════════════════════════════════════════════════════ --}}
<section class="section">
    <div class="container">
        <div class="screenshot-wrap">
            <img src="{{ asset('img/screenshots/hero.png') }}" alt="MovieShelf Oberfläche">
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ FEATURES ═══════════════════════════════════════════════════════ --}}
<section class="section-sm" id="features" style="border-top:1px solid var(--border)">
    <div class="container">
        <div style="text-align:center; margin-bottom:3rem">
            <span class="eyebrow">Features</span>
            <h2 class="display" style="font-size:clamp(1.8rem,4vw,2.8rem);margin:.5rem 0 0">Was MovieShelf kann.</h2>
            <div class="divider centered"></div>
        </div>

        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-collection-play"></i></div>
                <h3>Filmsammlung</h3>
                <p>Alle deine Filme auf einen Blick, sortiert und durchsuchbar.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-phone"></i></div>
                <h3>Android App</h3>
                <p>Native Android-App für unterwegs – dein Regal immer dabei.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div>
                <h3>Auto-Updates</h3>
                <p>Immer die neuste Version – ohne manuellen Aufwand.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h3>Backups inklusive</h3>
                <p>Deine Daten sind sicher und jederzeit wiederherstellbar.</p>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ COMPARE ═══════════════════════════════════════════════════════ --}}
<section class="section" style="border-top:1px solid var(--border)">
    <div class="container">
        <div style="text-align:center; margin-bottom:3rem">
            <span class="eyebrow">Optionen</span>
            <h2 class="display" style="font-size:clamp(1.8rem,4vw,2.8rem);margin:.5rem 0 0">Cloud oder Self-Hosted.</h2>
            <div class="divider centered"></div>
        </div>

        <div class="compare-grid">

            {{-- Self-Hosted --}}
            <div class="plan-card">
                <h3>Self-Hosted</h3>
                <p class="sub">Volle Kontrolle auf deiner eigenen Hardware.</p>
                <ul class="plan-list">
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>100 % Datenkontrolle</li>
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>Eigener Speicherplatz</li>
                    <li class="dim"><span class="check-icon bi bi-check" style="font-size:9px"></span>Server-Wartung nötig</li>
                    <li class="dim"><span class="check-icon bi bi-check" style="font-size:9px"></span>Manuelle Updates</li>
                </ul>
                <a href="https://github.com/lunasans/MovieShelf" target="_blank" rel="noopener" class="btn-outline">
                    Source Code ansehen
                </a>
            </div>

            {{-- Cloud --}}
            <div class="plan-card featured">
                <div class="plan-badge">Empfohlen</div>
                <h3>MovieShelf Cloud</h3>
                <p class="sub">Sofort nutzbar – ohne Setup, ohne Aufwand.</p>
                <ul class="plan-list">
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>Sofort einsatzbereit</li>
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>Automatische Updates</li>
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>Automatische Backups</li>
                    <li class="check"><span class="check-icon bi bi-check" style="font-size:9px"></span>Kostenloses Hosting</li>
                </ul>
                <button onclick="document.getElementById('subdomain').focus(); window.scrollTo({top:0,behavior:'smooth'})" class="btn-dark">
                    Jetzt registrieren
                </button>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ ANDROID BETA ═══════════════════════════════════════════════════════ --}}
<section class="section-sm beta-section">
    <div class="container">
        <div class="beta-inner">
            <div class="text">
                <div class="pulse-dot"><span></span>Android Beta</div>
                <h2 class="display" style="font-size:1.6rem;margin:.25rem 0 .4rem">Werde Beta-Tester!</h2>
                <p>Hilf uns die MovieShelf Android App zu perfektionieren und gestalte die Zukunft deiner Filmsammlung mit.</p>
            </div>
            <div class="beta-actions">
                <a href="mailto:support@movieshelf.info?subject=Android%20Beta%20Test"
                   class="btn-outline" style="display:inline-flex;align-items:center;gap:8px;padding:.65rem 1.25rem;width:auto">
                    <i class="bi bi-envelope"></i> Jetzt bewerben
                </a>
                <a href="https://play.google.com/store/apps/details?id=at.neuhaus.movieshelf&hl=de-DE"
                   target="_blank" rel="noopener">
                    <img src="https://play.google.com/intl/en_us/badges/static/images/badges/de_badge_web_generic.png"
                         alt="Get it on Google Play" style="height:52px;display:block">
                </a>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ FAQ ═══════════════════════════════════════════════════════ --}}
@if(isset($faqs) && $faqs->count() > 0)
<section class="section" id="faq" style="background: var(--surface); border-top:1px solid var(--border)">
    <div class="container">
        <div style="text-align:center; margin-bottom:4rem">
            <span class="eyebrow">Fragen</span>
            <h2 class="display" style="font-size:clamp(1.8rem,4vw,2.8rem);margin:.5rem 0 0">Antworten auf deine Fragen.</h2>
            <div class="divider centered"></div>
        </div>

        <div class="faq-container" x-data="{ active: null }">
            @foreach($faqs as $faq)
            <div class="faq-item" :class="{ 'active': active === {{ $loop->index }} }">
                <button class="faq-question" @click="active = (active === {{ $loop->index }} ? null : {{ $loop->index }})">
                    <span>{{ $faq->question }}</span>
                    <div class="faq-icon"></div>
                </button>
                <div class="faq-answer" x-show="active === {{ $loop->index }}" x-collapse>
                    <div class="py-4">
                        {!! $faq->answer !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════ CTA ═══════════════════════════════════════════════════════ --}}
<section class="cta-section">
    <div class="container">
        <span class="eyebrow">Loslegen</span>
        <h2 class="display" style="margin:.5rem 0 2rem">
            Bereit für dein<br>
            <span class="display-italic">digitales Filmregal?</span>
        </h2>
        <button onclick="document.getElementById('subdomain').focus(); window.scrollTo({top:0,behavior:'smooth'})"
                class="btn-cta">
            Cloud kostenlos starten <i class="bi bi-arrow-right"></i>
        </button>
        <p class="footer-note">
            MovieShelf Cloud · v2.12.3
            @if(\App\Models\Setting::get('saas_impressum_active', '0') == '1')
                · <a href="{{ route('saas.impressum') }}" style="color: inherit; text-decoration: underline;">Impressum</a>
            @endif
            · <a href="/privacy" style="color: inherit; text-decoration: underline;">Datenschutz</a>
        </p>
    </div>
</section>

@endsection