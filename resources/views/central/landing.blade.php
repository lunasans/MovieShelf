@extends('layouts.central')

@section('content')

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
                    const res  = await fetch('{{ route('api.check.subdomain', [], false) }}?name=' + this.subdomain);
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

        {{-- Tenant Login --}}
        <div class="tenant-login fade-up delay-4"
             x-data="{
                 tenant: '',
                 go() {
                     const slug = this.tenant.trim().toLowerCase();
                     if (slug.length > 1) window.location.href = 'https://' + slug + '.movieshelf.info';
                 }
             }">
            <div class="tenant-login-wrap">
                <span class="tenant-login-label">Bereits registriert?</span>
                <div class="tenant-login-field">
                    <input type="text"
                           x-model="tenant"
                           @keydown.enter="go()"
                           placeholder="dein-name"
                           autocomplete="off"
                           spellcheck="false">
                    <span class="tenant-login-suffix">.movieshelf.info</span>
                </div>
                <button class="tenant-login-btn" @click="go()" :disabled="tenant.trim().length < 2">
                    Zur Cloud <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- Trust Bar --}}
        <div class="trust-bar fade-up delay-4">
            <span><i class="bi bi-shield-check"></i> Kostenlos</span>
            <span><i class="bi bi-credit-card-2-front"></i> Keine Kreditkarte</span>
            <span><i class="bi bi-lightning-charge"></i> Sofort einsatzbereit</span>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════ SCREENSHOT SLIDER ═══════════════════════════════════════════════════════ --}}
<section class="section" style="border-top:1px solid var(--border)" x-data="{
    activeSlide: 0, 
    slides: [
        { src: '{{ asset('img/screenshots/hero.png') }}', alt: 'Dashboard Übersicht' },
        { src: '{{ asset('img/screenshots/grid.png') }}', alt: 'Filmgitter Ansicht' },
        { src: '{{ asset('img/screenshots/stats.png') }}', alt: 'Statistiken & Insights' }
    ],
    next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
    prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
    init() {
        setInterval(() => this.next(), 10000);
    }
}">
    <div class="container">
        <div style="text-align:center; margin-bottom:2.5rem">
            <span class="eyebrow">Einblick</span>
            <h2 class="display" style="font-size:clamp(1.8rem,4vw,2.8rem);margin:.5rem 0 0">So sieht es aus.</h2>
            <div class="divider centered"></div>
        </div>
        <div class="screenshot-wrap">
            
            {{-- Slides --}}
            <div class="slider-container" :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
                <template x-for="(slide, index) in slides" :key="index">
                    <div class="slider-slide">
                        <img :src="slide.src" :alt="slide.alt">
                    </div>
                </template>
            </div>

            {{-- Navigation --}}
            <div class="slider-nav">
                <button @click="prev()" class="slider-btn" aria-label="Vorheriges Bild">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button @click="next()" class="slider-btn" aria-label="Nächstes Bild">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            {{-- Dots --}}
            <div class="slider-dots">
                <template x-for="(slide, index) in slides" :key="index">
                    <div 
                        @click="activeSlide = index" 
                        class="dot" 
                        :class="activeSlide === index ? 'active' : ''"
                    ></div>
                </template>
            </div>

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
                    <li class="dim"><span class="check-icon bi bi-x" style="font-size:11px"></span>Server-Wartung nötig</li>
                    <li class="dim"><span class="check-icon bi bi-x" style="font-size:11px"></span>Manuelle Updates</li>
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
    </div>
</section>

@endsection