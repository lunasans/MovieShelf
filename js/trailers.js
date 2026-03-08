/**
 * Trailers Page Infinite Scroll
 * Centralized logic for loading more trailers
 */

(function () {
    function initTrailersInfiniteScroll() {
        const container = document.querySelector('.trailers-page');
        const trigger = document.getElementById('infiniteScrollTrigger');
        const loader = document.getElementById('infiniteScrollLoader');
        const displayedCount = document.getElementById('displayedCount');

        if (!container || !trigger) {
            // Not on trailers page
            return;
        }

        if (window.IS_DEV) console.log('🔄 Trailers Infinite Scroll initialisiert');

        let currentPage = parseInt(container.dataset.currentPage) || 1;
        const perPage = parseInt(container.dataset.perPage) || 12;
        const total = parseInt(container.dataset.total) || 0;

        let isLoading = false;
        let hasMore = (currentPage * perPage) < total;

        if (window.IS_DEV) console.log(`📊 Initial: Page ${currentPage}, PerPage ${perPage}, Total ${total}, HasMore ${hasMore}`);

        // Disconnect existing observer if any (for SPA)
        if (window._trailersObserver) {
            window._trailersObserver.disconnect();
        }

        // IntersectionObserver
        window._trailersObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hasMore && !isLoading) {
                    if (window.IS_DEV) console.log('👀 Trailers Trigger sichtbar - lade mehr...');
                    loadMore();
                }
            });
        }, {
            rootMargin: '200px'
        });

        window._trailersObserver.observe(trigger);

        async function loadMore() {
            if (isLoading || !hasMore) return;

            isLoading = true;
            currentPage++;

            if (window.IS_DEV) console.log(`⏳ Lade Trailers Seite ${currentPage}...`);

            if (loader) loader.style.display = 'block';

            try {
                const response = await fetch(`partials/trailers.php?ajax=1&p=${currentPage}`);
                const data = await response.json();

                if (data.success) {
                    const grid = document.querySelector('.trailers-grid');
                    if (grid && data.html) {
                        grid.insertAdjacentHTML('beforeend', data.html);

                        // Rebind links if needed
                        if (window.dvdApp && typeof window.dvdApp.updateNavigation === 'function') {
                            window.dvdApp.updateNavigation();
                        }

                        // Update counter
                        if (displayedCount) {
                            const currentDisplayed = parseInt(displayedCount.textContent) || 0;
                            displayedCount.textContent = currentDisplayed + data.loaded;
                        }

                        hasMore = data.hasMore;

                        if (!hasMore && trigger) {
                            trigger.style.display = 'none';
                        }
                    } else {
                        hasMore = false;
                    }
                } else {
                    hasMore = false;
                }
            } catch (error) {
                console.error('❌ Trailers Fetch-Fehler:', error);
                hasMore = false;
            } finally {
                if (loader) loader.style.display = 'none';
                isLoading = false;
            }
        }
    }

    // Init on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailersInfiniteScroll);
    } else {
        initTrailersInfiniteScroll();
    }

    // Export for SPA
    window.initTrailersInfiniteScroll = initTrailersInfiniteScroll;
})();
