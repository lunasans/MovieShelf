// main.js - Erweitert mit Film-Rating Funktionalität

class DVDApp {
    constructor() {
        this.container = document.getElementById('detail-container');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadFromUrl();
        this.restoreViewMode();
        this.updateNavigation();
    }

    // Event Handlers mit Event Delegation
    setupEventListeners() {
        // Event Delegation für Film-Details
        document.addEventListener('click', this.handleDocumentClick.bind(this));

        // Keyboard Events
        document.addEventListener('keydown', this.handleKeydown.bind(this));

        // Browser Navigation
        window.addEventListener('popstate', this.loadFromUrl.bind(this));

        // Search Form Handler
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const searchInput = searchForm.querySelector('input[name="q"]');
                const searchQuery = searchInput.value.trim();

                if (searchQuery) {
                    this.loadSearch(searchQuery);
                } else {
                    this.loadLatest();
                }
            });
        }
    }

    handleDocumentClick(e) {
        // Film Detail Toggle
        const toggleElement = e.target.closest('.toggle-detail');
        if (toggleElement) {
            e.preventDefault();
            const filmId = toggleElement.dataset.id;
            if (filmId) {
                this.loadFilmDetail(filmId);
            }
            return;
        }

        // Actor Profile Toggle - NEU
        const actorLink = e.target.closest('.actor-link, [data-actor-slug]');
        if (actorLink) {
            e.preventDefault();
            const actorSlug = actorLink.dataset.actorSlug || actorLink.getAttribute('data-actor-slug');
            if (actorSlug) {
                this.loadActorProfile(actorSlug);
            }
            return;
        }

        // Close Detail Button
        if (e.target.classList.contains('close-detail-button')) {
            e.preventDefault();
            this.closeDetail();
            return;
        }

        // Boxset Toggle
        if (e.target.classList.contains('boxset-toggle')) {
            e.preventDefault();
            this.handleBoxsetToggle(e.target);
            return;
        }

        // Route Links
        const routeLink = e.target.closest('.route-link');
        if (routeLink) {
            const href = routeLink.getAttribute('href');
            if (href && href.startsWith('?')) {
                e.preventDefault();
                history.pushState({}, '', href);
                this.loadFromUrl();
            }
            return;
        }

        // Tabs/Filter Links (in film-list.php) - laden in LINKE Seite
        const tabLink = e.target.closest('.tabs a');
        if (tabLink) {
            const href = tabLink.getAttribute('href');
            if (href && href.startsWith('?')) {
                e.preventDefault();
                history.pushState({}, '', href);
                this.loadPaginationPage(href);
            }
            return;
        }

        // Pagination Links - laden in LINKE Seite (film-list-area)
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            const href = paginationLink.getAttribute('href');
            if (href && href.startsWith('?')) {
                e.preventDefault();
                history.pushState({}, '', href);
                this.loadPaginationPage(href);
            }
            return;
        }

        // View Mode Toggle Buttons
        const viewBtn = e.target.closest('.view-btn');
        if (viewBtn) {
            e.preventDefault();
            const mode = viewBtn.dataset.mode;
            if (mode) {
                this.setViewMode(mode);
            }
            return;
        }

        // YouTube Trailer
        const placeholder = e.target.closest('.trailer-placeholder');
        if (placeholder) {
            this.loadTrailer(placeholder);
            return;
        }
    }

    handleKeydown(e) {
        if (e.key === 'Escape') {
            this.closeDetail();
        }
    }

    // Film Detail laden mit Rating-Integration
    async loadFilmDetail(filmId) {
        try {
            if (window.IS_DEV) console.log('🎬 Film-ID wird geladen:', filmId);

            const response = await fetch(`film-fragment.php?id=${filmId}`);
            const html = await response.text();

            if (window.IS_DEV) console.log('📄 Antwort erhalten, erste 100 Zeichen:', html.substring(0, 100));

            if (this.container) {
                this.container.innerHTML = html;
                history.replaceState(null, '', '?id=' + filmId);

                // Fancybox für neue Inhalte binden
                this.bindFancybox();

                // 🌟 FILM-RATING INITIALISIEREN
                this.initFilmRating();

                // 📺 STAFFELN/EPISODEN INITIALISIEREN
                this.initSeasons();

                // 🎭 ACTOR-LINKS NEU BINDEN (für Cast in film-view.php)
                this.rebindActorLinks();
            }
        } catch (error) {
            console.error('❌ Fehler beim Laden des Films:', error);
            if (this.container) {
                this.container.innerHTML = '<div style="color: red;">Fehler beim Laden des Films.</div>';
            }
        }
    }

    // 🎭 NEUE METHODE: Actor-Profil laden (analog zu loadFilmDetail)
    async loadActorProfile(actorSlug) {
        try {
            if (window.IS_DEV) console.log('🎭 Actor-Slug wird geladen:', actorSlug);

            const response = await fetch(`actor-fragment.php?slug=${encodeURIComponent(actorSlug)}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();

            if (window.IS_DEV) console.log('📄 Actor-Profil Antwort erhalten, erste 100 Zeichen:', html.substring(0, 100));

            if (this.container) {
                this.container.innerHTML = html;
                history.replaceState(null, '', '?page=actor&slug=' + actorSlug);

                // Fancybox für neue Inhalte binden
                this.bindFancybox();

                // Event-Listener für Actor-Links im geladenen Profil neu binden
                // (für Cast-Listen in Filmographie)
                this.rebindActorLinks();
            }
        } catch (error) {
            console.error('❌ Fehler beim Laden des Actor-Profils:', error);
            if (this.container) {
                this.container.innerHTML = `
                    <div class="error-message" style="padding: 40px; text-align: center;">
                        <div class="error-icon" style="font-size: 4rem; margin-bottom: 20px; color: #f48771;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h3 style="color: #f48771; margin-bottom: 15px;">Fehler beim Laden</h3>
                        <p style="margin-bottom: 25px;">Das Schauspieler-Profil konnte nicht geladen werden.</p>
                        <p style="font-size: 0.9em; opacity: 0.7;">${error.message}</p>
                        <div style="margin-top: 25px;">
                            <button onclick="location.reload()" class="btn btn-sm btn-primary" style="margin-right: 10px;">
                                <i class="bi bi-arrow-clockwise"></i> Erneut versuchen
                            </button>
                            <a href="/" class="btn btn-sm btn-secondary">
                                <i class="bi bi-house"></i> Zur Startseite
                            </a>
                        </div>
                    </div>
                `;
            }
        }
    }

    // 🎭 NEUE METHODE: Actor-Links im geladenen Content neu binden
    rebindActorLinks() {
        if (window.IS_DEV) console.log('🔗 Actor-Links werden neu gebunden...');
        // Event-Delegation funktioniert automatisch durch handleDocumentClick
        // Diese Methode ist für zukünftige Erweiterungen reserviert
    }

    // 🌟 NEUE METHODE: Film-Rating System initialisieren
    initFilmRating() {
        if (window.IS_DEV) console.log('🌟 Film Rating wird initialisiert...');

        // Rating-System
        const ratingStars = document.querySelectorAll('.rating-star');
        const saveRatingBtn = document.querySelector('.save-rating');
        const ratingDisplay = document.querySelector('.rating-display');
        const ratingInput = document.querySelector('.star-rating-input');

        if (!ratingStars.length) {
            if (window.IS_DEV) console.log('ℹ️ Keine Rating-Sterne gefunden (User nicht eingeloggt oder keine Rating-Sektion)');
            this.initOtherFilmFeatures(); // Andere Features trotzdem initialisieren
            return;
        }

        const currentRating = parseFloat(ratingInput?.dataset.currentRating || 0);
        let selectedRating = currentRating;

        if (window.IS_DEV) console.log('⭐ Rating System gefunden:', {
            ratingStars: ratingStars.length,
            saveRatingBtn: !!saveRatingBtn,
            ratingDisplay: !!ratingDisplay,
            currentRating: currentRating
        });

        // Event-Listener für Sterne
        ratingStars.forEach((star, index) => {
            star.style.cursor = 'pointer';

            star.addEventListener('mouseenter', () => {
                const rating = parseInt(star.dataset.rating);
                this.highlightStars(ratingStars, rating);
            });

            star.addEventListener('mouseleave', () => {
                this.highlightStars(ratingStars, selectedRating);
            });

            star.addEventListener('click', () => {
                selectedRating = parseInt(star.dataset.rating);
                if (window.IS_DEV) console.log('⭐ Stern geklickt, gewählte Bewertung:', selectedRating);

                this.highlightStars(ratingStars, selectedRating);

                if (saveRatingBtn) {
                    saveRatingBtn.style.display = 'inline-block';
                }
                if (ratingDisplay) {
                    ratingDisplay.textContent = selectedRating + '/5';
                }
            });
        });

        // Save-Button Event
        if (saveRatingBtn) {
            saveRatingBtn.addEventListener('click', () => {
                const filmId = ratingInput?.dataset.filmId;
                if (window.IS_DEV) console.log('💾 Speichere Rating:', { filmId, selectedRating });
                this.saveUserRating(filmId, selectedRating);
            });
        }

        // Andere Film-Features initialisieren
        this.initOtherFilmFeatures();
    }

    // Sterne hervorheben
    highlightStars(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill');
            } else {
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star');
            }
        });
    }

    // Andere Film-Features (Wishlist, Watched, Share, Trailer)
    initOtherFilmFeatures() {
        if (window.IS_DEV) console.log('🎭 Andere Film-Features werden initialisiert...');

        // Wishlist-Button
        const wishlistBtn = document.querySelector('.add-to-wishlist');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', () => {
                const filmId = wishlistBtn.dataset.filmId;
                this.toggleWishlist(filmId, wishlistBtn);
            });
        }

        // Watched-Button
        const watchedBtn = document.querySelector('.mark-as-watched');
        if (watchedBtn) {
            watchedBtn.addEventListener('click', () => {
                const filmId = watchedBtn.dataset.filmId;
                this.toggleWatched(filmId, watchedBtn);
            });
        }

        // Share-Button
        const shareBtn = document.querySelector('.share-film');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => {
                const filmId = shareBtn.dataset.filmId;
                const filmTitle = shareBtn.dataset.filmTitle;
                this.shareFilm(filmId, filmTitle);
            });
        }

    }

    // 📺 NEUE METHODE: Staffeln/Episoden Toggle initialisieren
    initSeasons() {
        if (window.IS_DEV) console.log('📺 Staffeln/Episoden wird initialisiert...');

        // Alle Season-Headers finden
        const headers = document.querySelectorAll('.season-header');
        if (window.IS_DEV) console.log('📺 Gefundene Staffel-Headers:', headers.length);

        if (headers.length === 0) {
            if (window.IS_DEV) console.log('ℹ️ Keine Staffeln gefunden (wahrscheinlich ein Film, keine Serie)');
            return;
        }

        // Event Listener für jeden Header hinzufügen
        headers.forEach(header => {
            const seasonNumber = header.getAttribute('data-season');
            if (window.IS_DEV) console.log('📺 Verarbeite Staffel:', seasonNumber);

            header.style.cursor = 'pointer';

            header.addEventListener('click', () => {
                if (window.IS_DEV) console.log('🖱️ Staffel geklickt:', seasonNumber);

                const content = document.querySelector(`[data-content="${seasonNumber}"]`);
                const caret = document.querySelector(`[data-caret="${seasonNumber}"]`);

                if (!content || !caret) {
                    console.error('❌ Elemente nicht gefunden für Staffel:', seasonNumber);
                    return;
                }

                // Toggle visibility
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    caret.classList.add('rotated');
                    if (window.IS_DEV) console.log('✅ Staffel', seasonNumber, 'geöffnet');
                } else {
                    content.style.display = 'none';
                    caret.classList.remove('rotated');
                    if (window.IS_DEV) console.log('✅ Staffel', seasonNumber, 'geschlossen');
                }
            });
        });

        // Erste Staffel automatisch aufklappen
        const firstCaret = document.querySelector('.season-caret');
        if (firstCaret) {
            firstCaret.classList.add('rotated');
            if (window.IS_DEV) console.log('✅ Erste Staffel ist aufgeklappt');
        }

        if (window.IS_DEV) console.log('✨ Staffeln/Episoden initialisierung abgeschlossen!');
    }

    // AJAX: User-Rating speichern
    async saveUserRating(filmId, rating) {
        if (window.IS_DEV) console.log('📡 AJAX: saveUserRating aufgerufen', { filmId, rating });

        try {
            const response = await fetch('api/save-rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ film_id: filmId, rating: rating })
            });

            if (window.IS_DEV) console.log('📡 Response status:', response.status);

            if (response.ok) {
                this.showNotification('Bewertung gespeichert!', 'success');
                const saveBtn = document.querySelector('.save-rating');
                if (saveBtn) saveBtn.style.display = 'none';

                // Seite nach kurzer Zeit neu laden um Community-Rating zu aktualisieren
                setTimeout(() => {
                    this.loadFilmDetail(filmId); // Reload der Film-Details
                }, 1500);
            } else {
                const errorText = await response.text();
                console.error('❌ Response error:', errorText);
                this.showNotification('Fehler beim Speichern: ' + response.status, 'error');
            }
        } catch (error) {
            console.error('❌ AJAX Error:', error);
            this.showNotification('Fehler beim Speichern der Bewertung', 'error');
        }
    }

    // AJAX: Wishlist Toggle
    async toggleWishlist(filmId, button) {
        try {
            const response = await fetch('api/toggle-wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ film_id: filmId })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.added) {
                    button.innerHTML = '<i class="bi bi-heart-fill"></i> Auf Wunschliste';
                    button.classList.add('active');
                    this.showNotification('Zur Wunschliste hinzugefügt!', 'success');
                } else {
                    button.innerHTML = '<i class="bi bi-heart"></i> Zur Wunschliste';
                    button.classList.remove('active');
                    this.showNotification('Von Wunschliste entfernt!', 'info');
                }
            }
        } catch (error) {
            this.showNotification('Fehler bei Wunschliste', 'error');
        }
    }

    // AJAX: Watched Toggle
    async toggleWatched(filmId, button) {
        try {
            const response = await fetch('api/toggle-watched.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ film_id: filmId })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.watched) {
                    button.innerHTML = '<i class="bi bi-check-circle-fill"></i> Gesehen';
                    button.classList.add('active');
                    this.showNotification('Als gesehen markiert!', 'success');
                } else {
                    button.innerHTML = '<i class="bi bi-check-circle"></i> Als gesehen markieren';
                    button.classList.remove('active');
                    this.showNotification('Markierung entfernt!', 'info');
                }
            }
        } catch (error) {
            this.showNotification('Fehler beim Markieren', 'error');
        }
    }

    // Share-Funktion
    shareFilm(filmId, filmTitle) {
        const url = window.location.origin + window.location.pathname + '?id=' + filmId;

        if (navigator.share) {
            navigator.share({
                title: filmTitle,
                text: 'Schau dir diesen Film an: ' + filmTitle,
                url: url
            });
        } else {
            navigator.clipboard.writeText(url).then(() => {
                this.showNotification('Link kopiert!', 'success');
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showNotification('Link kopiert!', 'success');
            });
        }
    }

    // Notification anzeigen
    showNotification(message, type = 'info') {
        if (window.IS_DEV) console.log(`🔔 Notification: ${message} (${type})`);

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.9);
            border: 1px solid rgba(255,255,255,0.1);
            border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            border-radius: 8px;
            color: white;
            z-index: 10000;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Detail schließen
    async closeDetail() {
        try {
            const response = await fetch('10-latest-fragment.php');
            const html = await response.text();

            if (this.container) {
                this.container.innerHTML = html;
            }
            history.replaceState(null, '', 'index.php');
        } catch (error) {
            console.error('Fehler beim Schließen:', error);
        }
    }

    // Boxset Toggle
    handleBoxsetToggle(button) {
        const dvdCard = button.closest('.dvd');
        const nextSibling = dvdCard?.nextElementSibling;

        if (nextSibling && nextSibling.classList.contains('boxset-children')) {
            const isOpen = nextSibling.classList.toggle('open');
            button.textContent = isOpen ?
                '▼ Box-Inhalte ausblenden' :
                '► Box-Inhalte anzeigen';
        }
    }

    // YouTube Trailer laden
    loadTrailer(placeholder) {
        const ytUrl = placeholder.dataset.yt;
        const iframe = document.createElement('iframe');
        iframe.src = ytUrl + '?autoplay=1';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.style.cssText = `
            width: 100%; 
            height: 100%; 
            border: none; 
            border-radius: 6px;
        `;
        placeholder.replaceWith(iframe);
    }

    // Routing bei Seitenwechsel
    async loadFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (!this.container) return;

        try {
            if (params.has('id')) {
                await this.loadFilmDetail(params.get('id'));

            } else if (params.has('page')) {
                await this.loadPage(params.get('page'));

            } else if (params.has('q') || params.has('type') || params.has('seite')) {
                // Suche, Filter oder Pagination → lade film-list.php
                await this.loadFilmList(params);

            } else {
                // Keine Parameter → lade 10 neueste
                await this.loadLatest();
            }
        } catch (error) {
            console.error('Fehler beim Laden der Seite:', error);
        }
    }

    async loadPage(page) {
        // Spezialbehandlung für Actor-Profile
        if (page === 'actor') {
            const params = new URLSearchParams(window.location.search);
            const slug = params.get('slug');

            if (slug) {
                // Lade Actor-Profil via loadActorProfile
                await this.loadActorProfile(slug);
            } else {
                // Kein Slug → Fehler
                this.container.innerHTML = `
                    <div class="error-message" style="padding: 40px; text-align: center;">
                        <div class="error-icon" style="font-size: 4rem; margin-bottom: 20px; color: #f48771;">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <h3 style="color: #f48771;">Kein Schauspieler angegeben</h3>
                        <p>Bitte wählen Sie einen Schauspieler aus.</p>
                    </div>
                `;
            }
            return;
        }

        // Standard-Verhalten für andere Pages (inkl. actors)
        // Query-Parameter durchreichen (außer 'page')
        const params = new URLSearchParams(window.location.search);
        params.delete('page'); // 'page' Parameter entfernen

        const queryString = params.toString();
        const url = queryString ? `partials/${page}.php?${queryString}` : `partials/${page}.php`;

        const response = await fetch(url);
        const html = await response.text();

        this.container.innerHTML = html;

        // Event-Handler für Actor-Links neu binden (für actors-Seite)
        if (page === 'actors') {
            this.rebindActorLinks();
        }

        if (page === 'stats') {
            await this.ensureChartJsLoaded();
            this.executeInlineScripts(this.container);
            if (typeof renderStatsCharts === 'function') {
                renderStatsCharts();
            }
        } else {
            this.executeInlineScripts(this.container);
        }
    }

    async loadLatestWithPagination(seite) {
        const response = await fetch(`10-latest-fragment.php?seite=${seite}`);
        const html = await response.text();
        this.container.innerHTML = html;
    }

    async loadLatest() {
        const response = await fetch('10-latest-fragment.php');
        const html = await response.text();
        this.container.innerHTML = html;
    }

    async loadSearch(query) {
        try {
            // Lade in film-list-area (LINKE Seite)
            const filmListArea = document.querySelector('.film-list-area');
            if (!filmListArea) {
                console.error('film-list-area nicht gefunden');
                return;
            }

            const response = await fetch(`partials/film-list.php?q=${encodeURIComponent(query)}`);
            const html = await response.text();
            filmListArea.innerHTML = html;

            // URL aktualisieren
            history.pushState({}, '', `?q=${encodeURIComponent(query)}`);

            // Restore View Mode
            this.restoreViewMode();

            if (window.IS_DEV) console.log(`🔍 Suche nach: "${query}"`);
        } catch (error) {
            console.error('Suchfehler:', error);
            const filmListArea = document.querySelector('.film-list-area');
            if (filmListArea) {
                filmListArea.innerHTML = '<div class="alert alert-danger">Fehler bei der Suche</div>';
            }
        }
    }

    async loadFilmList(params) {
        try {
            // Lade in film-list-area (LINKE Seite)
            const filmListArea = document.querySelector('.film-list-area');
            if (!filmListArea) {
                console.error('film-list-area nicht gefunden');
                return;
            }

            // Baue URL mit allen Parametern (q, type, seite)
            const queryString = params.toString();
            const response = await fetch(`partials/film-list.php?${queryString}`);
            const html = await response.text();
            filmListArea.innerHTML = html;

            // Restore View Mode nach Laden
            this.restoreViewMode();

            if (window.IS_DEV) console.log(`📋 Film-Liste geladen: ${queryString}`);
        } catch (error) {
            console.error('Film-List Fehler:', error);
            const filmListArea = document.querySelector('.film-list-area');
            if (filmListArea) {
                filmListArea.innerHTML = '<div class="alert alert-danger">Fehler beim Laden</div>';
            }
        }
    }

    async loadPaginationPage(href) {
        try {
            // Lade in film-list-area (LINKE Seite), nicht in detail-container!
            const filmListArea = document.querySelector('.film-list-area');
            if (!filmListArea) {
                console.error('film-list-area nicht gefunden');
                return;
            }

            const response = await fetch(`partials/film-list.php${href}`);
            const html = await response.text();
            filmListArea.innerHTML = html;

            // Restore View Mode
            this.restoreViewMode();

            if (window.IS_DEV) console.log(`📄 Pagination geladen: ${href}`);

            // BoxSet Modal Drag neu initialisieren nach AJAX-Reload
            if (typeof window.reinitBoxSetModal === 'function') {
                window.reinitBoxSetModal();
            }
        } catch (error) {
            console.error('Pagination Fehler:', error);
        }
    }

    // Fancybox binden
    bindFancybox() {
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind("[data-fancybox]", {});
        }
    }

    // Chart.js laden
    async ensureChartJsLoaded() {
        if (window.Chart) return;

        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = () => {
                if (window.IS_DEV) console.log('📊 Chart.js geladen');
                resolve();
            };
            document.head.appendChild(script);
        });
    }

    // Restliche Methoden (updateNavigation, restoreViewMode, executeInlineScripts) bleiben unverändert...
    updateNavigation() {
        const links = document.querySelectorAll('.main-nav a');
        const current = window.location.search;

        links.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === current) {
                link.classList.add('active');
            }
        });
    }

    setViewMode(mode) {
        const list = document.querySelector('.film-list');
        if (!list) return;

        // Entferne alte Klassen
        list.classList.remove('grid-mode', 'list-mode');
        list.classList.add(mode + '-mode');

        // Speichere Präferenz
        localStorage.setItem('viewMode', mode);

        // Update Button States
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.mode === mode) {
                btn.classList.add('active');
            }
        });

        if (window.IS_DEV) console.log(`📋 View-Modus: ${mode}`);
    }

    restoreViewMode() {
        const savedMode = localStorage.getItem('viewMode') || 'grid';
        this.setViewMode(savedMode);
    }

    executeInlineScripts(container) {
        if (!container) return;

        // Finde alle <script> Tags im Container
        const scripts = container.querySelectorAll('script');

        scripts.forEach(oldScript => {
            // Erstelle neues Script-Element
            const newScript = document.createElement('script');

            // Kopiere alle Attribute
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });

            // Kopiere Inline-Code oder src
            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent;
            }

            // Ersetze altes Script mit neuem (damit es ausgeführt wird)
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        if (window.IS_DEV) console.log(`✅ ${scripts.length} Inline-Scripts ausgeführt`);
    }
}

// App initialisieren
document.addEventListener('DOMContentLoaded', () => {
    //    console.log('🚀 DVD App wird initialisiert...');
    window.dvdApp = new DVDApp();
});

// Global verfügbare Funktion für closeDetail (für Backwards-Kompatibilität)
function closeDetail() {
    if (window.dvdApp) {
        window.dvdApp.closeDetail();
    }
}

// Global verfügbare Funktion für setViewMode
window.setViewMode = function (mode) {
    if (window.dvdApp) {
        window.dvdApp.setViewMode(mode);
    }
};