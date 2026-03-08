/**
 * Actors Overview Infinite Scroll
 * Centralized from actors.php
 */

(function () {
    function initActorsInfiniteScroll() {
        const container = document.querySelector('.actors-overview-container');
        const trigger = document.getElementById('infiniteScrollTrigger');
        const loader = document.getElementById('infiniteScrollLoader');
        const displayedCount = document.getElementById('displayedCount');

        if (!container || !trigger) {
            // Falls wir nicht auf der Actors-Seite sind, einfach beenden
            return;
        }

        if (window.IS_DEV) console.log('🔄 Actors Infinite Scroll initialisiert');

        let currentPage = parseInt(container.dataset.currentPage) || 1;
        const perPage = parseInt(container.dataset.perPage) || 50;
        const total = parseInt(container.dataset.total) || 0;
        const letter = container.dataset.letter || '';

        let isLoading = false;
        let hasMore = (currentPage * perPage) < total;

        if (window.IS_DEV) console.log(`📊 Initial: Page ${currentPage}, PerPage ${perPage}, Total ${total}, HasMore ${hasMore}`);

        // Bestehende Observer trennen, falls vorhanden (bei SPA Re-Init)
        if (window._actorsObserver) {
            window._actorsObserver.disconnect();
        }

        // IntersectionObserver für Infinite Scroll
        window._actorsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hasMore && !isLoading) {
                    if (window.IS_DEV) console.log('👀 Actors Trigger sichtbar - lade mehr...');
                    loadMore();
                }
            });
        }, {
            rootMargin: '200px'
        });

        window._actorsObserver.observe(trigger);

        async function loadMore() {
            if (isLoading || !hasMore) return;

            isLoading = true;
            currentPage++;

            if (window.IS_DEV) console.log(`⏳ Lade Actors Seite ${currentPage}...`);

            loader.style.display = 'block';

            try {
                let url = `partials/actors.php?ajax=1&p=${currentPage}`;
                if (letter) {
                    url += `&letter=${encodeURIComponent(letter)}`;
                }

                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    // Finde die richtige actors-grid
                    let actorsGrid;
                    if (letter) {
                        actorsGrid = document.querySelector('.letter-group .actors-grid');
                    } else {
                        const letterGroups = document.querySelectorAll('.letter-group');
                        if (letterGroups.length > 0) {
                            actorsGrid = letterGroups[letterGroups.length - 1].querySelector('.actors-grid');
                        }
                    }

                    if (actorsGrid && data.html) {
                        actorsGrid.insertAdjacentHTML('beforeend', data.html);

                        // Rebind Actor-Links (für SPA)
                        if (window.dvdApp && typeof window.dvdApp.rebindActorLinks === 'function') {
                            window.dvdApp.rebindActorLinks();
                        }

                        // Update Counter
                        const currentDisplayed = parseInt(displayedCount.textContent) || 0;
                        displayedCount.textContent = currentDisplayed + data.loaded;

                        hasMore = data.hasMore;

                        if (!hasMore) {
                            trigger.style.display = 'none';
                        }
                    } else {
                        hasMore = false;
                    }
                } else {
                    hasMore = false;
                }
            } catch (error) {
                console.error('❌ Actors Fetch-Fehler:', error);
                hasMore = false;
            } finally {
                loader.style.display = 'none';
                isLoading = false;
            }
        }
    }

    // Init bei Load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initActorsInfiniteScroll);
    } else {
        initActorsInfiniteScroll();
    }

    // Globaler Export für SPA Re-Init
    window.initActorsInfiniteScroll = initActorsInfiniteScroll;
})();
