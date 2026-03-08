// main.js - Zentralisiertes JavaScript für die DVD Profiler Liste
// Enthält Logik für Film-Details, BoxSets, Ratings, Trailer und View-Modi

class DVDApp {
    constructor() {
        this.container = document.getElementById('detail-container');
        this.pendingTrailer = null; // Für Altersverifizierung
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadFromUrl();
        this.restoreViewMode();
        this.updateNavigation();
        this.initBoxSetModal();
    }

    // Modal Drag properties
    dragData = {
        isDragging: false,
        currentX: 0,
        currentY: 0,
        initialX: 0,
        initialY: 0,
        xOffset: 0,
        yOffset: 0
    };

    // Event Handlers mit Event Delegation
    setupEventListeners() {
        // Event Delegation für Film-Details und allgemeine Klicks
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

        // Actor Profile Toggle
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

        // Boxset Toggle (in der Liste)
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

        // Tabs/Filter Links (in film-list.php)
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

        // Pagination Links
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

        // Boxset Toggle (Badge auf Filmkarte)
        const boxsetBadge = e.target.closest('.boxset-badge');
        if (boxsetBadge) {
            e.preventDefault();
            const parentId = boxsetBadge.dataset.parentId;
            if (parentId) {
                this.openBoxSetModal(e, parentId);
            }
            return;
        }

        // BoxSet Modal Close
        if (e.target.closest('.modal-close')) {
            e.preventDefault();
            this.closeBoxSetModal();
            return;
        }

        // YouTube Trailer (aus film-view.php)
        const trailerBox = e.target.closest('.trailer-box');
        if (trailerBox && !trailerBox.closest('.trailers-grid')) {
            e.preventDefault();
            this.handleTrailerClick(trailerBox);
            return;
        }

        // Generische Trailer Placeholder
        const placeholder = e.target.closest('.trailer-placeholder');
        if (placeholder) {
            this.loadTrailer(placeholder);
            return;
        }
    }

    handleKeydown(e) {
        if (e.key === 'Escape') {
            this.closeDetail();
            this.closeBoxSetModal();
        }
    }

    // Film Detail laden
    async loadFilmDetail(filmId) {
        try {
            if (window.IS_DEV) console.log('🎬 Film-ID wird geladen:', filmId);

            const response = await fetch(`film-fragment.php?id=${filmId}`);
            const html = await response.text();

            if (this.container) {
                this.container.innerHTML = html;
                history.replaceState(null, '', '?id=' + filmId);

                this.bindFancybox();
                this.initFilmRating();
                this.initSeasons();
                this.rebindActorLinks();
            }
        } catch (error) {
            console.error('❌ Fehler beim Laden des Films:', error);
            if (this.container) {
                this.container.innerHTML = '<div style="color: red;">Fehler beim Laden des Films.</div>';
            }
        }
    }

    // Actor-Profil laden
    async loadActorProfile(actorSlug) {
        try {
            if (window.IS_DEV) console.log('🎭 Actor-Slug wird geladen:', actorSlug);

            const response = await fetch(`actor-fragment.php?slug=${encodeURIComponent(actorSlug)}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();

            if (this.container) {
                this.container.innerHTML = html;
                history.replaceState(null, '', '?page=actor&slug=' + actorSlug);
                this.bindFancybox();
                this.rebindActorLinks();
            }
        } catch (error) {
            console.error('❌ Fehler beim Laden des Actor-Profils:', error);
        }
    }

    rebindActorLinks() {
        if (window.IS_DEV) console.log('🔗 Actor-Links rebunden...');
    }

    // Rating System initialisieren
    initFilmRating() {
        const ratingStars = document.querySelectorAll('.rating-star');
        const saveRatingBtn = document.querySelector('.save-rating');
        const ratingDisplay = document.querySelector('.rating-display');
        const ratingInput = document.querySelector('.star-rating-input');

        if (!ratingStars.length) {
            this.initOtherFilmFeatures();
            return;
        }

        const currentRating = parseFloat(ratingInput?.dataset.currentRating || 0);
        let selectedRating = currentRating;

        ratingStars.forEach((star) => {
            star.style.cursor = 'pointer';
            star.addEventListener('mouseenter', () => this.highlightStars(ratingStars, parseInt(star.dataset.rating)));
            star.addEventListener('mouseleave', () => this.highlightStars(ratingStars, selectedRating));
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.dataset.rating);
                this.highlightStars(ratingStars, selectedRating);
                if (saveRatingBtn) saveRatingBtn.style.display = 'inline-block';
                if (ratingDisplay) ratingDisplay.textContent = selectedRating + '/5';
            });
        });

        if (saveRatingBtn) {
            saveRatingBtn.addEventListener('click', () => {
                const filmId = ratingInput?.dataset.filmId;
                this.saveUserRating(filmId, selectedRating);
            });
        }

        this.initOtherFilmFeatures();
    }

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

    initOtherFilmFeatures() {
        // Wishlist
        const wishlistBtn = document.querySelector('.add-to-wishlist');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', () => this.toggleWishlist(wishlistBtn.dataset.filmId, wishlistBtn));
        }

        // Watched
        const watchedBtn = document.querySelector('.mark-as-watched');
        if (watchedBtn) {
            watchedBtn.addEventListener('click', () => this.toggleWatched(watchedBtn.dataset.filmId, watchedBtn));
        }

        // Share
        const shareBtn = document.querySelector('.share-film');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => this.shareFilm(shareBtn.dataset.filmId, shareBtn.dataset.filmTitle));
        }

        // Age Verification Setup
        this.setupAgeButtons();
    }

    initSeasons() {
        const headers = document.querySelectorAll('.season-header');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const seasonNumber = header.getAttribute('data-season');
                const content = document.querySelector(`[data-content="${seasonNumber}"]`);
                const caret = document.querySelector(`[data-caret="${seasonNumber}"]`);

                if (content && caret) {
                    const isHidden = content.style.display === 'none';
                    content.style.display = isHidden ? 'block' : 'none';
                    isHidden ? caret.classList.add('rotated') : caret.classList.remove('rotated');
                }
            });
        });

        const firstCaret = document.querySelector('.season-caret');
        if (firstCaret) firstCaret.classList.add('rotated');
    }

    async saveUserRating(filmId, rating) {
        try {
            const response = await fetch('api/save-rating.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ film_id: filmId, rating: rating })
            });

            if (response.ok) {
                this.showNotification('Bewertung gespeichert!', 'success');
                setTimeout(() => this.loadFilmDetail(filmId), 1500);
            } else {
                this.showNotification('Fehler beim Speichern', 'error');
            }
        } catch (error) {
            console.error('AJAX Error:', error);
        }
    }

    async toggleWishlist(filmId, button) {
        try {
            const response = await fetch('api/toggle-wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ film_id: filmId })
            });

            if (response.ok) {
                const result = await response.json();
                button.innerHTML = result.added ? '<i class="bi bi-heart-fill"></i> Auf Wunschliste' : '<i class="bi bi-heart"></i> Zur Wunschliste';
                button.classList.toggle('active', result.added);
                this.showNotification(result.added ? 'Hinzugefügt!' : 'Entfernt!', 'success');
            }
        } catch (error) {
            this.showNotification('Fehler bei Wunschliste', 'error');
        }
    }

    async toggleWatched(filmId, button) {
        try {
            const response = await fetch('api/toggle-watched.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ film_id: filmId })
            });

            if (response.ok) {
                const result = await response.json();
                button.innerHTML = result.watched ? '<i class="bi bi-check-circle-fill"></i> Gesehen' : '<i class="bi bi-check-circle"></i> Markieren';
                button.classList.toggle('active', result.watched);
                this.showNotification(result.watched ? 'Als gesehen markiert!' : 'Markierung entfernt!', 'success');
            }
        } catch (error) {
            this.showNotification('Fehler bei Watched', 'error');
        }
    }

    shareFilm(filmId, filmTitle) {
        const url = window.location.origin + window.location.pathname + '?id=' + filmId;
        if (navigator.share) {
            navigator.share({ title: filmTitle, url: url });
        } else {
            navigator.clipboard.writeText(url).then(() => this.showNotification('Link kopiert!', 'success'));
        }
    }

    showNotification(message, type = 'info') {
        const n = document.createElement('div');
        n.className = `notification notification-${type}`;
        n.textContent = message;
        n.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.9); border-radius: 8px; color: white;
            z-index: 10000; backdrop-filter: blur(10px); transform: translateX(120%);
            transition: transform 0.3s ease-out; border-left: 4px solid ${type === 'success' ? '#28a745' : '#dc3545'};
        `;
        document.body.appendChild(n);
        setTimeout(() => n.style.transform = 'translateX(0)', 10);
        setTimeout(() => {
            n.style.transform = 'translateX(120%)';
            setTimeout(() => n.remove(), 300);
        }, 3000);
    }

    async closeDetail() {
        try {
            const response = await fetch('10-latest-fragment.php');
            const html = await response.text();
            if (this.container) this.container.innerHTML = html;
            history.replaceState(null, '', 'index.php');
        } catch (e) {
            console.error(e);
        }
    }

    handleBoxsetToggle(button) {
        const dvd = button.closest('.dvd');
        const children = dvd?.nextElementSibling;
        if (children?.classList.contains('boxset-children')) {
            const isOpen = children.classList.toggle('open');
            button.textContent = isOpen ? '▼ Ausblenden' : '► Anzeigen';
        }
    }

    loadTrailer(placeholder) {
        const ytUrl = placeholder.dataset.yt;
        const iframe = document.createElement('iframe');
        iframe.src = ytUrl + (ytUrl.includes('?') ? '&' : '?') + 'autoplay=1';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.style.cssText = 'width: 100%; height: 100%; border: none; border-radius: 6px;';
        placeholder.replaceWith(iframe);
    }

    handleTrailerClick(box) {
        const age = parseInt(box.dataset.ratingAge || 0);
        const url = box.dataset.src;
        const confirmed = document.cookie.includes('age_confirmed_18=true');

        if (age >= 18 && !confirmed) {
            const modal = document.getElementById('ageVerificationModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                this.pendingTrailer = { box, url };
            }
        } else {
            this.playTrailerNow(box, url);
        }
    }

    setupAgeButtons() {
        const confirm = document.getElementById('ageConfirmBtn');
        const deny = document.getElementById('ageDenyBtn');
        const modal = document.getElementById('ageVerificationModal');

        if (!modal) return;

        if (confirm && !confirm.dataset.listener) {
            confirm.dataset.listener = 'true';
            confirm.addEventListener('click', () => {
                const d = new Date(); d.setDate(d.getDate() + 30);
                document.cookie = `age_confirmed_18=true; expires=${d.toUTCString()}; path=/; SameSite=Strict`;
                modal.style.display = 'none';
                document.body.style.overflow = '';
                if (this.pendingTrailer) {
                    this.playTrailerNow(this.pendingTrailer.box, this.pendingTrailer.url);
                    this.pendingTrailer = null;
                }
            });
        }
        if (deny && !deny.dataset.listener) {
            deny.dataset.listener = 'true';
            deny.addEventListener('click', () => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                this.pendingTrailer = null;
            });
        }
    }

    playTrailerNow(box, url) {
        if (!url) return;
        const container = box.closest('.trailer-container');
        if (!container) return;

        let embed = url;
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const id = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/)?.[1];
            if (id) embed = `https://www.youtube.com/embed/${id}?autoplay=1&modestbranding=1`;
        }

        const iframe = document.createElement('iframe');
        iframe.src = embed;
        iframe.width = '100%';
        iframe.style.aspectRatio = '16/9';
        iframe.style.border = 'none';
        iframe.style.borderRadius = '8px';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;

        box.style.display = 'none';
        container.appendChild(iframe);
    }

    // Routing & Loading
    async loadFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (!this.container) return;

        if (params.has('id')) await this.loadFilmDetail(params.get('id'));
        else if (params.has('page')) await this.loadPage(params.get('page'));
        else if (params.has('q') || params.has('type') || params.has('seite')) await this.loadFilmList(params);
        else await this.loadLatest();

        this.updateNavigation();
    }

    async loadPage(page) {
        if (page === 'actor') {
            const slug = new URLSearchParams(window.location.search).get('slug');
            if (slug) await this.loadActorProfile(slug);
            return;
        }

        const params = new URLSearchParams(window.location.search);
        params.delete('page');
        const url = `partials/${page}.php${params.toString() ? '?' + params.toString() : ''}`;

        try {
            const response = await fetch(url);
            this.container.innerHTML = await response.text();
            if (page === 'actors') this.rebindActorLinks();

            if (page === 'stats') {
                await this.ensureChartJsLoaded();
                this.executeInlineScripts(this.container);
                if (typeof renderStatsCharts === 'function') renderStatsCharts();
            } else {
                this.executeInlineScripts(this.container);
            }
        } catch (e) { console.error(e); }
    }

    async loadLatest() {
        const r = await fetch('10-latest-fragment.php');
        if (this.container) this.container.innerHTML = await r.text();
    }

    async loadSearch(query) {
        const area = document.querySelector('.film-list-area');
        if (!area) return;
        const r = await fetch(`partials/film-list.php?q=${encodeURIComponent(query)}`);
        area.innerHTML = await r.text();
        history.pushState({}, '', `?q=${encodeURIComponent(query)}`);
        this.restoreViewMode();
    }

    async loadFilmList(params) {
        const area = document.querySelector('.film-list-area');
        if (!area) return;
        const r = await fetch(`partials/film-list.php?${params.toString()}`);
        area.innerHTML = await r.text();
        this.restoreViewMode();
    }

    async loadPaginationPage(href) {
        const area = document.querySelector('.film-list-area');
        if (!area) return;
        const r = await fetch(`partials/film-list.php${href}`);
        area.innerHTML = await r.text();
        this.restoreViewMode();
    }

    // Utility
    bindFancybox() {
        if (typeof Fancybox !== 'undefined') Fancybox.bind("[data-fancybox]", {});
    }

    async ensureChartJsLoaded() {
        if (window.Chart) return;
        return new Promise(r => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.onload = r;
            document.head.appendChild(s);
        });
    }

    setViewMode(mode) {
        const list = document.querySelector('.film-list');
        if (!list) return;
        list.classList.remove('grid-mode', 'list-mode');
        list.classList.add(mode + '-mode');
        document.documentElement.setAttribute('data-view-mode', mode);
        localStorage.setItem('movieViewMode', mode);
        document.querySelectorAll('.view-btn').forEach(b => b.classList.toggle('active', b.dataset.mode === mode));
    }

    restoreViewMode() {
        const m = localStorage.getItem('movieViewMode') || 'grid';
        this.setViewMode(m);
    }

    executeInlineScripts(c) {
        if (!c) return;
        c.querySelectorAll('script').forEach(s => {
            const n = document.createElement('script');
            Array.from(s.attributes).forEach(a => n.setAttribute(a.name, a.value));
            if (s.src) n.src = s.src; else n.textContent = s.textContent;
            s.parentNode.replaceChild(n, s);
        });
    }

    // Navigation Active Link Handling
    updateNavigation() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 'home';
        const search = params.get('q');

        document.querySelectorAll('.main-nav .route-link').forEach(link => {
            const href = link.getAttribute('href');
            let isActive = false;

            if (page === 'home' && (href === 'index.php' || href === '/')) {
                // Nur aktiv wenn keine Suche aktiv ist (sonst ist "Start" zu oft aktiv)
                isActive = !search;
            } else if (href && href.includes(`page=${page}`)) {
                isActive = true;
            }

            link.classList.toggle('active', isActive);
        });
    }

    // 📦 BOXSET MODAL LOGIC
    initBoxSetModal() {
        const modal = document.getElementById('boxsetModal');
        if (modal) {
            if (modal.parentElement !== document.body) document.body.appendChild(modal);
            this.initBoxSetModalDrag();
        }
    }

    async openBoxSetModal(event, parentId) {
        const modal = document.getElementById('boxsetModal');
        const title = document.getElementById('modalTitle');
        const body = document.getElementById('modalBody');
        const content = modal?.querySelector('.modal-content');
        if (!modal || !content) return;

        document.body.style.overflow = 'hidden';
        modal.classList.add('show');
        content.style.opacity = '0';

        try {
            const r = await fetch(`partials/boxset-children.php?parent_id=${parentId}`);
            const d = await r.json();
            if (title) title.innerHTML = `<i class="bi bi-arrows-move drag-handle"></i> <span>${d.parent_title} (${d.children.length})</span>`;
            if (body) {
                let html = '<div class="modal-films-grid">';
                d.children.forEach(f => html += this.renderModalFilmCard(f));
                html += '</div>';
                body.innerHTML = html;
            }

            requestAnimationFrame(() => {
                const w = content.offsetWidth, h = content.offsetHeight;
                let x = Math.max(20, Math.min(event.clientX - 50, window.innerWidth - w - 20));
                let y = Math.max(20, Math.min(event.clientY - 50, window.innerHeight - h - 20));
                this.dragData.xOffset = x; this.dragData.yOffset = y;
                content.style.transform = `translate(${x}px, ${y}px)`;
                content.style.opacity = '1';
            });
        } catch (e) {
            if (body) body.innerHTML = '❌ Fehler';
            content.style.opacity = '1';
        }
    }

    closeBoxSetModal() {
        const m = document.getElementById('boxsetModal');
        if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
    }

    renderModalFilmCard(f) {
        let b = '';
        if (f.tmdb_rating > 0) {
            let color = f.tmdb_rating >= 8 ? '#4caf50' : (f.tmdb_rating >= 6 ? '#ff9800' : '#f44336');
            b = `<div class="tmdb-rating-badge" style="background:${color}"><i class="bi bi-star-fill"></i> ${parseFloat(f.tmdb_rating).toFixed(1)}</div>`;
        }
        return `<div class="dvd"><div class="cover-area"><img src="${f.cover}">${b}</div><div class="dvd-details"><h2><a href="#" class="toggle-detail" data-id="${f.id}">${f.title}</a></h2></div></div>`;
    }

    initBoxSetModalDrag() {
        const m = document.getElementById('boxsetModal'), c = m?.querySelector('.modal-content');
        if (!c) return;
        const start = (e) => {
            if (!e.target.closest('.modal-header') || e.target.closest('.modal-close')) return;
            this.dragData.isDragging = true;
            const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;
            this.dragData.initialX = clientX - this.dragData.xOffset;
            this.dragData.initialY = clientY - this.dragData.yOffset;
        };
        const move = (e) => {
            if (!this.dragData.isDragging) return;
            e.preventDefault();
            const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;
            this.dragData.xOffset = clientX - this.dragData.initialX;
            this.dragData.yOffset = clientY - this.dragData.initialY;
            c.style.transform = `translate(${this.dragData.xOffset}px, ${this.dragData.yOffset}px)`;
        };
        const end = () => this.dragData.isDragging = false;
        m.addEventListener('mousedown', start); m.addEventListener('mousemove', move); window.addEventListener('mouseup', end);
        m.addEventListener('touchstart', start, { passive: false }); m.addEventListener('touchmove', move, { passive: false }); window.addEventListener('touchend', end);
    }
}

// Global Helpers
document.addEventListener('DOMContentLoaded', () => { window.dvdApp = new DVDApp(); });
function closeDetail() { if (window.dvdApp) window.dvdApp.closeDetail(); }
window.setViewMode = function (m) { if (window.dvdApp) window.dvdApp.setViewMode(m); };
window.openBoxSetModal = function (e, p) { if (window.dvdApp) window.dvdApp.openBoxSetModal(e, p); };
window.IS_DEV = false; // Setze auf true für Logs
