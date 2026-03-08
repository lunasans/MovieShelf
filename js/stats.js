/**
 * Stats Page Charts Logic
 * Centralized from stats.php
 */

function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('⏳ Chart.js noch nicht geladen, warte...');
        setTimeout(initializeCharts, 100);
        return;
    }

    if (!window.STATS_DATA) {
        console.warn('⚠️ Keine Statistik-Daten gefunden. Warte auf Daten-Skript...');
        setTimeout(initializeCharts, 100);
        return;
    }

    if (window.IS_DEV) console.log('✅ Chart.js und Daten verfügbar, starte Initialisierung');

    // Canvas-Elemente suchen
    const collectionCanvas = document.getElementById('collectionChart');
    const ratingCanvas = document.getElementById('ratingChart');
    const genreCanvas = document.getElementById('genreChart');
    const yearCanvas = document.getElementById('yearChart');

    if (!collectionCanvas && !ratingCanvas && !genreCanvas && !yearCanvas) {
        console.warn('🚫 Keine Diagramm-Canvas-Elemente im DOM gefunden.');
        return;
    }

    if (window.IS_DEV) console.log('🎯 Canvas-Elemente:', {
        collection: !!collectionCanvas,
        rating: !!ratingCanvas,
        genre: !!genreCanvas,
        year: !!yearCanvas
    });

    // Daten von window.STATS_DATA laden (wird inline in stats.php gesetzt)
    const chartData = window.STATS_DATA || {
        collections: [],
        ratings: [],
        genres: [],
        years: {}
    };

    if (window.IS_DEV) console.log('📊 Daten geladen:', chartData);

    // Farben definieren
    const colors = [
        '#3498db', '#2ecc71', '#f39c12', '#e74c3c',
        '#9b59b6', '#1abc9c', '#34495e', '#95a5a6'
    ];

    // Basis-Optionen
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#ffffff',
                    usePointStyle: true,
                    padding: 15
                }
            }
        }
    };

    // 1. Collection Chart (Doughnut)
    if (collectionCanvas && chartData.collections.length > 0) {
        try {
            // Alte Instanz zerstören falls vorhanden (SPA-Fix)
            const existingChart = Chart.getChart(collectionCanvas);
            if (existingChart) existingChart.destroy();

            new Chart(collectionCanvas, {
                type: 'doughnut',
                data: {
                    labels: chartData.collections.map(c => c.collection_type),
                    datasets: [{
                        data: chartData.collections.map(c => parseInt(c.count)),
                        backgroundColor: colors.slice(0, chartData.collections.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: Object.assign({}, baseOptions, {
                    cutout: '60%'
                })
            });
        } catch (e) {
            console.error('❌ Collection Chart Fehler:', e);
        }
    }

    // 2. Rating Chart (Bar)
    if (ratingCanvas && chartData.ratings.length > 0) {
        try {
            // Alte Instanz zerstören falls vorhanden (SPA-Fix)
            const existingChart = Chart.getChart(ratingCanvas);
            if (existingChart) existingChart.destroy();

            new Chart(ratingCanvas, {
                type: 'bar',
                data: {
                    labels: chartData.ratings.map(r => 'FSK ' + r.rating_age),
                    datasets: [{
                        label: 'Anzahl Filme',
                        data: chartData.ratings.map(r => parseInt(r.count)),
                        backgroundColor: colors[1],
                        borderColor: colors[1],
                        borderWidth: 1
                    }]
                },
                options: Object.assign({}, baseOptions, {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        x: {
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        }
                    }
                })
            });
        } catch (e) {
            console.error('❌ Rating Chart Fehler:', e);
        }
    }

    // 3. Genre Chart (Horizontal Bar)
    if (genreCanvas && chartData.genres.length > 0) {
        try {
            // Alte Instanz zerstören falls vorhanden (SPA-Fix)
            const existingChart = Chart.getChart(genreCanvas);
            if (existingChart) existingChart.destroy();

            new Chart(genreCanvas, {
                type: 'bar',
                data: {
                    labels: chartData.genres.map(g => g.genre),
                    datasets: [{
                        label: 'Anzahl Filme',
                        data: chartData.genres.map(g => parseInt(g.count)),
                        backgroundColor: colors[0],
                        borderColor: colors[0],
                        borderWidth: 1
                    }]
                },
                options: Object.assign({}, baseOptions, {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        y: {
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        }
                    }
                })
            });
        } catch (e) {
            console.error('❌ Genre Chart Fehler:', e);
        }
    }

    // 4. Year Chart (Line)
    if (yearCanvas && Object.keys(chartData.years).length > 0) {
        try {
            // Alte Instanz zerstören falls vorhanden (SPA-Fix)
            const existingChart = Chart.getChart(yearCanvas);
            if (existingChart) existingChart.destroy();

            new Chart(yearCanvas, {
                type: 'line',
                data: {
                    labels: Object.keys(chartData.years),
                    datasets: [{
                        label: 'Filme pro Jahr',
                        data: Object.values(chartData.years).map(v => parseInt(v)),
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: colors[0],
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: colors[0],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: Object.assign({}, baseOptions, {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#ffffff',
                                callback: function (value) {
                                    return Number.isInteger(value) ? value : '';
                                }
                            },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        x: {
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        }
                    }
                })
            });
        } catch (e) {
            console.error('❌ Year Chart Fehler:', e);
        }
    }
}

// Initialisierung bei DOMContentLoaded oder sofort wenn bereits geladen
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.search.includes('page=stats')) {
        initializeCharts();
    }
});
