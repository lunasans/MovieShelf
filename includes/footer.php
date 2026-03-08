<?php
/**
 * MovieShelf - Footer
 * Vereinfachte und aufgeräumte Version
 * 
 * @package    movieshelf
 * @author     René Neuhaus
 * @version    1.5.0
 */

require_once __DIR__ . '/version.php';
require_once __DIR__ . '/bootstrap.php';

// Versionsinformationen
$currentVersion = DVDPROFILER_VERSION;
$author = DVDPROFILER_AUTHOR;
$githubUrl = DVDPROFILER_GITHUB_URL;
$repository = DVDPROFILER_REPOSITORY;

// Statistiken sammeln
$stats = getDVDProfilerStatistics();

// Counter-Werte (falls neue counter.php verwendet wird)
$totalVisits = $GLOBALS['total_visits'] ?? ($stats['total_visits'] ?? 0);
$dailyVisits = $GLOBALS['daily_visits'] ?? 0;

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<footer class="site-footer" role="contentinfo">
    <div class="footer-content">
        <!-- Obere Reihe: Nur Navigation -->
        <div class="footer-top">
            <nav class="footer-nav" aria-label="Footer Navigation">
                <a href="?page=impressum"><i class="bi bi-info-circle"></i> Impressum</a>
                <a href="?page=datenschutz"><i class="bi bi-shield-lock"></i> Datenschutz</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= $baseUrl ?>/admin/" rel="nofollow"><i class="bi bi-speedometer2"></i> Admin</a>
                    <a href="<?= $baseUrl ?>/admin/logout.php" rel="nofollow"><i class="bi bi-box-arrow-right"></i> Logout</a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/admin/login.php" rel="nofollow"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Mittlere Reihe: Statistiken -->
        <div class="footer-center">
            <div class="footer-stats">
                <div class="stat-item" title="Filme in der Sammlung">
                    <i class="bi bi-collection"></i>
                    <span><?= number_format($stats['total_films']) ?> Filme</span>
                </div>
                
                <div class="stat-item" title="Website Besucher gesamt">
                    <i class="bi bi-eye"></i>
                    <span><?= number_format($totalVisits) ?> Besucher</span>
                </div>
                
                <?php if ($dailyVisits > 0): ?>
                <div class="stat-item" title="Besucher heute">
                    <i class="bi bi-calendar-day"></i>
                    <span><?= number_format($dailyVisits) ?> heute</span>
                </div>
                <?php endif; ?>
                
                <div class="stat-item" title="Verschiedene Genres">
                    <i class="bi bi-tags"></i>
                    <span><?= number_format($stats['total_genres']) ?> Genres</span>
                </div>
            </div>
        </div>

        <!-- Untere Reihe: Meta, Copyright & TMDB (Vertikal gestapelt) -->
        <div class="footer-bottom">
            <div class="footer-meta-stack">
                <div class="version-info">
                    <span class="version">v<?= $currentVersion ?></span>
                    <a href="<?= htmlspecialchars($githubUrl) ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       title="Auf GitHub ansehen"
                       class="github-link">
                        <i class="bi bi-github"></i>
                    </a>
                </div>
                
                <div class="copyright">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($author) ?>
                </div>

                <div class="tmdb-attribution">
                    <span>This website uses TMDB and the TMDB APIs but is not endorsed or certified by TMDB.</span>
                    <a href="https://www.themoviedb.org" target="_blank" rel="noopener noreferrer">
                        <img src="<?= SVG_PATH ?>/tmdb_logo.svg" alt="TMDB Logo" class="tmdb-logo">
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="scroll-progress" role="progressbar" aria-label="Scroll-Fortschritt"></div>
</footer>

<!-- Theme Switcher -->
<div class="theme-switcher" id="themeSwitcher">
    <button class="theme-toggle-btn" id="themeToggleBtn" aria-label="Theme wechseln" title="Theme wechseln">
        <i class="bi bi-palette-fill"></i>
    </button>
    
    <div class="theme-picker" id="themePicker">
        <div class="theme-picker-header">
            <span>🎨 Theme wählen</span>
            <button class="close-picker" aria-label="Schließen">×</button>
        </div>
        
        <div class="theme-options">
            <button class="theme-option" data-theme="default" title="Standard Theme">
                <div class="theme-preview" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <span class="theme-name">Standard</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="dark" title="Dark Mode">
                <div class="theme-preview" style="background: linear-gradient(135deg, #bb86fc 0%, #3700b3 100%);"></div>
                <span class="theme-name">Dark</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="blue" title="Blue Ocean">
                <div class="theme-preview" style="background: linear-gradient(135deg, #00d4ff 0%, #0080ff 100%);"></div>
                <span class="theme-name">Blue</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="green" title="Matrix Green">
                <div class="theme-preview" style="background: linear-gradient(135deg, #00ff41 0%, #00aa2b 100%);"></div>
                <span class="theme-name">Green</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="red" title="Warm Red">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ff4757 0%, #c0392b 100%);"></div>
                <span class="theme-name">Red</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="purple" title="Royal Purple">
                <div class="theme-preview" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);"></div>
                <span class="theme-name">Purple</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
        </div>
        
        <div class="theme-picker-divider">
            <span>✨ Saison-Themes</span>
        </div>
        
        <div class="theme-options season-themes">
            <button class="theme-option" data-theme="christmas" title="Weihnachten 🎄">
                <div class="theme-preview" style="background: linear-gradient(135deg, #c41e3a 0%, #165b33 50%, #c41e3a 100%);"></div>
                <span class="theme-name">🎄 Weihnachten</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="newyear" title="Silvester 🎆">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ffd700 0%, #ff6b6b 50%, #4ecdc4 100%);"></div>
                <span class="theme-name">🎆 Silvester</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="valentine" title="Valentinstag 💝">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ff1744 0%, #f50057 100%);"></div>
                <span class="theme-name">💝 Valentinstag</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="easter" title="Ostern 🐰">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ffd3a5 0%, #fd6585 100%);"></div>
                <span class="theme-name">🐰 Ostern</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="summer" title="Sommer ☀️">
                <div class="theme-preview" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <span class="theme-name">☀️ Sommer</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
            
            <button class="theme-option" data-theme="halloween" title="Halloween 🎃">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ff6600 0%, #8b00ff 100%);"></div>
                <span class="theme-name">🎃 Halloween</span>
                <i class="bi bi-check-circle-fill theme-check"></i>
            </button>
        </div>
        
        <div class="theme-picker-footer">
            <small>Theme wird automatisch gespeichert</small>
        </div>
    </div>
</div>

<script>
// Footer JavaScript - Scroll Progress
(function() {
    const scrollProgress = document.querySelector('.scroll-progress');
    
    if (scrollProgress) {
        window.addEventListener('scroll', function() {
            const winHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
            
            scrollProgress.style.width = Math.min(Math.max(scrollPercent, 0), 100) + '%';
        }, { passive: true });
    }
})();
</script>

<style>
/* ============================================
   Footer Styles - Aufgeräumt & Modern
   ============================================ */

.site-footer {
    position: relative;
    background: var(--glass-bg-strong, rgba(20, 20, 30, 0.95));
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border-top: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
    margin-top: var(--space-2xl, 3rem);
    padding: var(--space-xl, 2rem) 0;
    color: var(--text-glass, rgba(255, 255, 255, 0.7));
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--space-lg, 1.5rem);
    display: flex;
    flex-direction: column;
    gap: var(--space-xl, 2rem);
}

/* Obere Reihe: Navigation zentriert */
.footer-top {
    display: flex;
    justify-content: center;
    align-items: center;
    border-bottom: 1px solid var(--glass-border, rgba(255, 255, 255, 0.05));
    padding-bottom: var(--space-lg, 1.5rem);
}

.footer-nav {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.footer-nav a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.1rem;
    color: var(--text-glass, rgba(255, 255, 255, 0.7));
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: var(--radius-lg, 12px);
    background: var(--glass-bg, rgba(255, 255, 255, 0.03));
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.05));
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.footer-nav a:hover {
    color: var(--text-white, #ffffff);
    background: var(--glass-bg-strong, rgba(255, 255, 255, 0.1));
    border-color: var(--accent-color, #667eea);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.footer-nav a i {
    font-size: 1.1rem;
    color: var(--accent-color, #667eea);
}

/* Mittlere Reihe: Statistiken */
.footer-center {
    display: flex;
    justify-content: center;
}

.footer-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: var(--space-md, 1rem);
    width: 100%;
    max-width: 900px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: var(--space-md, 1rem);
    background: var(--glass-bg, rgba(255, 255, 255, 0.03));
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.05));
    border-radius: var(--radius-xl, 20px);
    transition: all 0.4s ease;
}

.stat-item:hover {
    background: var(--glass-bg-strong, rgba(255, 255, 255, 0.08));
    border-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.stat-item i {
    font-size: 1.5rem;
    background: var(--gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-item span {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-white, #ffffff);
}

/* Untere Reihe: Meta-Stack (Vertikal) */
.footer-bottom {
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: var(--space-lg, 1.5rem);
    border-top: 1px solid var(--glass-border, rgba(255, 255, 255, 0.05));
    font-size: 0.8rem;
}

.footer-meta-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    text-align: center;
}

.version-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.version {
    background: var(--gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    color: white;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.75rem;
}

.github-link {
    font-size: 1.2rem;
    color: var(--text-glass, rgba(255, 255, 255, 0.5));
    transition: color 0.3s ease;
}

.github-link:hover {
    color: var(--text-white, #ffffff);
}

.copyright {
    color: var(--text-glass, rgba(255, 255, 255, 0.4));
    font-weight: 500;
}

.tmdb-attribution {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    max-width: 600px;
    color: var(--text-glass, rgba(255, 255, 255, 0.35));
}

.tmdb-attribution span {
    line-height: 1.4;
}

.tmdb-logo {
    height: 1.1rem;
    width: auto;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.tmdb-attribution a:hover .tmdb-logo {
    opacity: 1;
}

/* Scroll Progress */
.scroll-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: var(--gradient-primary, linear-gradient(90deg, #667eea 0%, #764ba2 100%));
    box-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
}

/* Responsive */
@media (max-width: 992px) {
    .footer-top, .footer-bottom {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
    }
    
    .tmdb-attribution {
        text-align: center;
    }
}

/* ============================================
   Responsive Design
   ============================================ */

@media (max-width: 992px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: var(--space-lg, 1.5rem);
        text-align: center;
    }
    
    .footer-section {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .footer-center {
        order: -1; /* Statistiken + Version zuerst */
    }
    
    .footer-stats {
        justify-content: center;
    }
    
    .footer-nav {
        flex-wrap: wrap;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .site-footer {
        padding: var(--space-lg, 1.5rem) var(--space-md, 1rem);
    }
    
    .footer-stats {
        gap: var(--space-xs, 0.35rem);
    }
    
    .stat-item {
        font-size: 0.8rem;
        padding: 0.25rem 0.4rem;
    }
    
    .stat-item span {
        display: none; /* Nur Icons auf sehr kleinen Screens */
    }
    
    .footer-nav {
        gap: var(--space-sm, 0.5rem);
        font-size: 0.85rem;
    }
    
    .footer-nav a {
        padding: 0.25rem 0.5rem;
    }
}

/* ============================================
   Print Styles
   ============================================ */

@media print {
    .site-footer {
        background: none;
        border-top: 1px solid #000;
        padding: 1rem 0;
    }
    
    .footer-stats,
    .footer-nav,
    .scroll-progress {
        display: none;
    }
    
    .footer-content {
        display: block;
    }
}
</style>
<script>
// Theme Switcher JavaScript
(function() {
    const themeSwitcher = document.getElementById('themeSwitcher');
    const toggleBtn = document.getElementById('themeToggleBtn');
    const themePicker = document.getElementById('themePicker');
    const themeOptions = document.querySelectorAll('.theme-option');
    const closeBtn = document.querySelector('.close-picker');
    const html = document.documentElement;
    
    // Zeige Theme Switcher erst wenn alles geladen ist
    setTimeout(() => {
        themeSwitcher.style.opacity = '1';
        themeSwitcher.style.visibility = 'visible';
    }, 300);
    
    // Aktuelles Theme aus HTML-Attribut lesen
    let currentTheme = html.getAttribute('data-theme') || 'default';
    
    // Aktives Theme markieren
    function updateActiveTheme() {
        themeOptions.forEach(option => {
            const theme = option.getAttribute('data-theme');
            option.classList.toggle('active', theme === currentTheme);
        });
    }
    
    // Initial aktives Theme setzen
    updateActiveTheme();
    
    // Theme Picker öffnen/schließen
    toggleBtn.addEventListener('click', () => {
        themePicker.classList.toggle('show');
    });
    
    closeBtn.addEventListener('click', () => {
        themePicker.classList.remove('show');
    });
    
    // Außerhalb klicken schließt Picker
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.theme-switcher')) {
            themePicker.classList.remove('show');
        }
    });
    
    // Theme wechseln
    themeOptions.forEach(option => {
        option.addEventListener('click', async () => {
            const newTheme = option.getAttribute('data-theme');
            
            if (newTheme === currentTheme) return;
            
            // Theme sofort visuell anwenden
            html.setAttribute('data-theme', newTheme);
            currentTheme = newTheme;
            updateActiveTheme();
            
            // Theme in Datenbank speichern (AJAX)
            try {
                const formData = new FormData();
                formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>');
                formData.append('theme', newTheme);
                
                const response = await fetch('theme-save.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (window.IS_DEV) console.log('Theme saved:', result);
                } else {
                    console.error('Theme save failed:', response.status);
                }
            } catch (error) {
                console.error('Theme save error:', error);
            }
            
            // Picker nach 500ms schließen
            setTimeout(() => {
                themePicker.classList.remove('show');
            }, 500);
        });
    });
})();
</script>

<style>
/* Theme Switcher Styles */
.theme-switcher {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.theme-toggle-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-primary, #667eea), var(--accent-hover, #764ba2));
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 0 0 rgba(102, 126, 234, 0.5);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse-shadow 2s infinite;
}

.theme-toggle-btn:hover {
    transform: scale(1.1) rotate(15deg);
    box-shadow: 0 6px 30px rgba(0, 0, 0, 0.4), 0 0 0 8px rgba(102, 126, 234, 0.2);
}

.theme-toggle-btn:active {
    transform: scale(0.95);
}

@keyframes pulse-shadow {
    0%, 100% {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 0 0 rgba(102, 126, 234, 0.5);
    }
    50% {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 0 8px rgba(102, 126, 234, 0.2);
    }
}

.theme-picker {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 320px;
    background: var(--bg-secondary, #1a1a2e);
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.9);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.theme-picker.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.theme-picker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
    font-weight: 600;
    color: var(--text-primary, #e4e4e7);
}

.close-picker {
    background: none;
    border: none;
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
    font-size: 28px;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.close-picker:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary, #e4e4e7);
}

.theme-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    padding: 16px;
}

.theme-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: var(--bg-tertiary, #16213e);
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.theme-option:hover {
    background: var(--bg-primary, #0f0f23);
    border-color: var(--accent-primary, #667eea);
    transform: translateY(-2px);
}

.theme-option.active {
    border-color: var(--accent-primary, #667eea);
    background: var(--bg-primary, #0f0f23);
}

.theme-preview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.theme-name {
    font-size: 14px;
    color: var(--text-primary, #e4e4e7);
    font-weight: 500;
}

.theme-check {
    position: absolute;
    top: 8px;
    right: 8px;
    color: var(--accent-primary, #667eea);
    font-size: 18px;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.theme-option.active .theme-check {
    opacity: 1;
    transform: scale(1);
    animation: success-pulse 0.5s ease;
}

@keyframes success-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.theme-picker-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
    text-align: center;
}

.theme-picker-footer small {
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
    font-size: 11px;
}

.theme-picker-divider {
    padding: 12px 20px;
    text-align: center;
    border-top: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
    font-size: 0.85rem;
    color: var(--text-muted, rgba(228, 228, 231, 0.7));
    font-weight: 600;
}

.season-themes {
    padding-bottom: 8px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .theme-switcher {
        bottom: 70px;
        right: 15px;
    }
    
    .theme-toggle-btn {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .theme-picker {
        width: 280px;
        bottom: 65px;
    }
    
    .theme-options {
        gap: 10px;
        padding: 12px;
    }
    
    .theme-preview {
        width: 50px;
        height: 50px;
    }
}
</style>