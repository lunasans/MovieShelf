<x-admin-layout>
    @section('header_title', 'System-Einstellungen')

    <div class="max-w-5xl mx-auto" x-data="{ activeTab: 'general' }">
        <!-- Tab Navigation -->
        <div class="flex overflow-x-auto custom-scrollbar-hide md:flex-wrap gap-3 mb-10 bg-white/5 p-2 rounded-2xl border border-white/10 backdrop-blur-md">
            <button @click="activeTab = 'general'" 
                :class="activeTab === 'general' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-gear-fill"></i> Allgemein
            </button>
            <button @click="activeTab = 'tmdb'" 
                :class="activeTab === 'tmdb' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-star-fill"></i> TMDb
            </button>
            <button @click="activeTab = 'legal'" 
                :class="activeTab === 'legal' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-info-circle-fill"></i> Rechtliches
            </button>
            <button @click="activeTab = 'signature'" 
                :class="activeTab === 'signature' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-card-image"></i> Signatur
            </button>
            <button @click="activeTab = 'mail'" 
                :class="activeTab === 'mail' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-envelope-fill"></i> E-Mail / Server
            </button>
            <button @click="activeTab = 'backup'" 
                :class="activeTab === 'backup' ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-white/30 hover:text-white hover:bg-white/5'" 
                class="flex items-center gap-2 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap shrink-0">
                <i class="bi bi-cloud-download-fill"></i> Backup
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- General Settings -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    <div class="flex items-center gap-6 mb-12">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white text-2xl shadow-xl shadow-rose-600/20 ring-2 ring-white/10">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-white tracking-tight uppercase">Allgemeine Konfiguration</h2>
                            <p class="text-sm text-white/40 font-medium tracking-wide">Grundlegende Einstellungen für deine Filmsammlung.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="md:col-span-2">
                            <label for="site_title" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Website Titel</label>
                            <input type="text" name="site_title" id="site_title" value="{{ old('site_title', $settings['site_title'] ?? 'MovieShelf') }}" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white text-lg font-bold focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                        </div>
                        <div>
                            <label for="items_per_page" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Filme pro Seite</label>
                            <input type="number" name="items_per_page" id="items_per_page" value="{{ old('items_per_page', $settings['items_per_page'] ?? '20') }}" required min="5" max="100" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div>
                            <label for="latest_films_count" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Anzahl neueste Filme</label>
                            <input type="number" name="latest_films_count" id="latest_films_count" value="{{ old('latest_films_count', $settings['latest_films_count'] ?? '15') }}" required min="5" max="50" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div>
                            <label for="default_view_mode" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Standard-Ansicht</label>
                            <select name="default_view_mode" id="default_view_mode" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="grid" {{ (old('default_view_mode', $settings['default_view_mode'] ?? 'grid') == 'grid') ? 'selected' : '' }} class="bg-zinc-900">Grid (Kacheln)</option>
                                <option value="list" {{ (old('default_view_mode', $settings['default_view_mode'] ?? 'grid') == 'list') ? 'selected' : '' }} class="bg-zinc-900">Liste</option>
                            </select>
                        </div>
                        <div>
                            <label for="default_guest_layout" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Standard Gast-Layout</label>
                            <select name="default_guest_layout" id="default_guest_layout" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="classic" {{ (old('default_guest_layout', $settings['default_guest_layout'] ?? 'classic') == 'classic') ? 'selected' : '' }} class="bg-zinc-900">Klassisch</option>
                                <option value="streaming" {{ (old('default_guest_layout', $settings['default_guest_layout'] ?? 'classic') == 'streaming') ? 'selected' : '' }} class="bg-zinc-900">Streaming</option>
                            </select>
                        </div>
                        <div>
                            <label for="theme" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Standard-Theme</label>
                            <select name="theme" id="theme" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="default" {{ (old('theme', $settings['theme'] ?? 'default') == 'default') ? 'selected' : '' }} class="bg-zinc-900 text-white">Standard (Rose Red)</option>
                                <option value="dark" {{ (old('theme', $settings['theme'] ?? 'default') == 'dark') ? 'selected' : '' }} class="bg-zinc-900">Dark</option>
                                <option value="blue" {{ (old('theme', $settings['theme'] ?? 'default') == 'blue') ? 'selected' : '' }} class="bg-zinc-900">Blue Ocean</option>
                                <option value="green" {{ (old('theme', $settings['theme'] ?? 'default') == 'green') ? 'selected' : '' }} class="bg-zinc-900">Green Nature</option>
                                <option value="red" {{ (old('theme', $settings['theme'] ?? 'default') == 'red') ? 'selected' : '' }} class="bg-zinc-900">Red Velocity</option>
                                <option value="summer" {{ (old('theme', $settings['theme'] ?? 'default') == 'summer') ? 'selected' : '' }} class="bg-zinc-900">Summer Breeze</option>
                            </select>
                        </div>
                        <div>
                            <label for="boxset_quick_view_style" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Boxset Quick-View Stil</label>
                            <select name="boxset_quick_view_style" id="boxset_quick_view_style" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="island" {{ (old('boxset_quick_view_style', $settings['boxset_quick_view_style'] ?? 'island') == 'island') ? 'selected' : '' }} class="bg-zinc-900">Floating Island (Modern)</option>
                                <option value="modal" {{ (old('boxset_quick_view_style', $settings['boxset_quick_view_style'] ?? 'island') == 'modal') ? 'selected' : '' }} class="bg-zinc-900">Modal (Klassisch v1.5)</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 space-y-6 pt-6 mt-6 border-t border-white/5">
                            <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[1.5rem] border border-white/10 group hover:border-rose-500/30 transition-all cursor-pointer">
                                <input type="checkbox" name="telemetry_enabled" id="telemetry_enabled" value="1" {{ (old('telemetry_enabled', $settings['telemetry_enabled'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                                <label for="telemetry_enabled" class="flex-1 cursor-pointer">
                                    <span class="block text-sm font-black text-white uppercase tracking-widest">Anonyme Statistiken senden</span>
                                    <span class="text-[10px] text-white/30 font-medium italic mt-1 block tracking-wide">Hilf mit, das System zu verbessern. Rein technische, anonyme Daten.</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[1.5rem] border border-white/10 group hover:border-rose-500/30 transition-all cursor-pointer">
                                <input type="checkbox" name="migration_enabled" id="migration_enabled" value="1" {{ (old('migration_enabled', $settings['migration_enabled'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                                <label for="migration_enabled" class="flex-1 cursor-pointer">
                                    <span class="block text-sm font-black text-white uppercase tracking-widest">Datenmigration V1.5</span>
                                    <span class="text-[10px] text-white/30 font-medium italic mt-1 block tracking-wide">Zeigt den Menüpunkt zur Datenmigration aus Version 1.5 in der Sidebar an.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TMDb Integration -->
            <div x-show="activeTab === 'tmdb'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    <div class="flex items-center gap-6 mb-12">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-amber-500/20 ring-2 ring-white/10">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-white tracking-tight uppercase">TMDb API Zugriff</h2>
                            <p class="text-sm text-white/40 font-medium tracking-wide">Automatische Datenanreicherung von TheMovieDatabase.</p>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div>
                            <label for="tmdb_api_key" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">API Key (v3)</label>
                            <input type="password" name="tmdb_api_key" id="tmdb_api_key" value="{{ old('tmdb_api_key', $settings['tmdb_api_key'] ?? '') }}" placeholder="••••••••••••••••••••" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white font-mono focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div class="p-8 bg-black/20 border border-white/5 rounded-[2rem] flex flex-col md:flex-row items-center gap-6">
                            <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center shrink-0">
                                <i class="bi bi-lightbulb text-amber-400 text-2xl"></i>
                            </div>
                            <p class="text-xs text-white/50 leading-relaxed font-medium">
                                Den API-Schlüssel findest du in deinem Account auf <a href="https://www.themoviedb.org/" target="_blank" class="text-rose-400 hover:text-rose-300 underline font-black">themoviedb.org</a>. Ohne diesen Key können keine Cover oder Filmdetails automatisch archiviert werden.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legal Settings -->
            <div x-show="activeTab === 'legal'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    <div class="flex items-center gap-6 mb-12">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-600 to-red-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-rose-600/20 ring-2 ring-white/10">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-white tracking-tight uppercase">Rechtliches & Compliance</h2>
                            <p class="text-sm text-white/40 font-medium tracking-wide">Rechtliche Hinweise und Betreiberangaben.</p>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div>
                                <label for="impressum_name" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Name / Betreiber</label>
                                <input type="text" name="impressum_name" id="impressum_name" value="{{ old('impressum_name', $settings['impressum_name'] ?? '') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                            <div>
                                <label for="impressum_email" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Kontakt E-Mail</label>
                                <input type="email" name="impressum_email" id="impressum_email" value="{{ old('impressum_email', $settings['impressum_email'] ?? '') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                        </div>
                        <div>
                            <label for="impressum_content" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Inhalt (HTML erlaubt)</label>
                            <textarea name="impressum_content" id="impressum_content" rows="4" class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">{{ old('impressum_content', $settings['impressum_content'] ?? '') }}</textarea>
                        </div>
                        <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[1.5rem] border border-white/10 cursor-pointer group hover:border-rose-500/30 transition-all">
                            <input type="checkbox" name="impressum_enabled" id="impressum_enabled" value="1" {{ (old('impressum_enabled', $settings['impressum_enabled'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                            <label for="impressum_enabled" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Impressum öffentlich anzeigen</label>
                        </div>

                        <!-- Cookie Banner -->
                        <div class="border-t border-white/5 pt-8 mt-4">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-lg shadow-lg shadow-cyan-500/20 ring-2 ring-white/10">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3 class="text-lg font-black text-white uppercase tracking-widest">Cookie-Banner</h3>
                            </div>
                            <div class="space-y-6">
                                <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[1.5rem] border border-white/10 cursor-pointer group hover:border-rose-500/30 transition-all">
                                    <input type="checkbox" name="cookie_banner_enabled" id="cookie_banner_enabled" value="1" {{ (old('cookie_banner_enabled', $settings['cookie_banner_enabled'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                                    <label for="cookie_banner_enabled" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Cookie-Banner aktivieren</label>
                                </div>
                                <div>
                                    <label for="cookie_banner_text" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Banner Text</label>
                                    <textarea name="cookie_banner_text" id="cookie_banner_text" rows="3" placeholder="Wir verwenden Cookies, um die Benutzererfahrung zu verbessern..." class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">{{ old('cookie_banner_text', $settings['cookie_banner_text'] ?? 'Diese Website verwendet Cookies, um die bestmögliche Erfahrung zu bieten.') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Tab -->
            <div x-show="activeTab === 'signature'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    <div class="flex items-center gap-6 mb-12">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white text-2xl shadow-xl shadow-rose-600/20 ring-2 ring-white/10">
                            <i class="bi bi-card-image"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-white tracking-tight uppercase">Signatur-Banner</h2>
                            <p class="text-sm text-white/40 font-medium tracking-wide">Dynamische Foren-Banner Konfiguration.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[1.5rem] border border-white/10 h-fit group hover:border-rose-500/30 transition-all">
                            <input type="checkbox" name="signature_enabled" id="signature_enabled" value="1" {{ (old('signature_enabled', $settings['signature_enabled'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                            <label for="signature_enabled" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Aktivieren</label>
                        </div>
                        <div>
                            <label for="signature_film_count" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Anzahl Filme</label>
                            <input type="number" name="signature_film_count" id="signature_film_count" value="{{ old('signature_film_count', $settings['signature_film_count'] ?? '10') }}" min="1" max="20" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div>
                            <label for="signature_film_source" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Daten-Quelle</label>
                            <select name="signature_film_source" id="signature_film_source" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="newest" {{ ($settings['signature_film_source'] ?? 'newest') == 'newest' ? 'selected' : '' }} class="bg-zinc-900">Neueste (Archiv)</option>
                                <option value="newest_release" {{ ($settings['signature_film_source'] ?? 'newest') == 'newest_release' ? 'selected' : '' }} class="bg-zinc-900">Neueste (Erscheinungsjahr)</option>
                                <option value="random" {{ ($settings['signature_film_source'] ?? 'random') == 'random' ? 'selected' : '' }} class="bg-zinc-900">Zufällig</option>
                            </select>
                        </div>
                        <div>
                            <label for="signature_cache_time" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Cache-Zeit (Sekunden)</label>
                            <input type="number" name="signature_cache_time" id="signature_cache_time" value="{{ old('signature_cache_time', $settings['signature_cache_time'] ?? '3600') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                    </div>

                    <!-- Display Options -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 border-t border-white/5 pt-8">
                        <div class="flex items-center gap-4 bg-white/5 p-5 rounded-[1.5rem] border border-white/10 group hover:border-rose-500/30 transition-all cursor-pointer">
                            <input type="checkbox" name="signature_show_title" id="signature_show_title" value="1" {{ (old('signature_show_title', $settings['signature_show_title'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                            <label for="signature_show_title" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Titel anzeigen</label>
                        </div>
                        <div class="flex items-center gap-4 bg-white/5 p-5 rounded-[1.5rem] border border-white/10 group hover:border-rose-500/30 transition-all cursor-pointer">
                            <input type="checkbox" name="signature_show_year" id="signature_show_year" value="1" {{ (old('signature_show_year', $settings['signature_show_year'] ?? '1') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                            <label for="signature_show_year" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Jahr anzeigen</label>
                        </div>
                        <div class="flex items-center gap-4 bg-white/5 p-5 rounded-[1.5rem] border border-white/10 group hover:border-rose-500/30 transition-all cursor-pointer">
                            <input type="checkbox" name="signature_show_rating" id="signature_show_rating" value="1" {{ (old('signature_show_rating', $settings['signature_show_rating'] ?? '0') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                            <label for="signature_show_rating" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer">Bewertung anzeigen</label>
                        </div>
                    </div>
                    
                    <!-- Preview Area -->
                    <div class="mt-12 space-y-8">
                        <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] flex items-center gap-2">
                            <i class="bi bi-eye"></i> Live-Vorschau
                        </h3>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] italic">Typ 1 (Klassisch)</p>
                                <div class="p-6 bg-black/20 rounded-[2rem] border border-white/5 overflow-hidden">
                                    <img src="{{ route('signature') }}?type=1&t={{ time() }}" alt="Banner Type 1" class="max-w-full h-auto rounded-xl shadow-2xl border border-white/10">
                                </div>
                                <div class="mt-3 space-y-3 px-1">
                                    <div>
                                        <label class="text-[9px] text-white/20 uppercase tracking-widest font-black mb-1 block">BBCode für Foren</label>
                                        <input type="text" readonly value="[url={{ url('/') }}][img]{{ route('signature') }}?type=1[/img][/url]"
                                               class="w-full bg-black/40 border border-white/10 rounded-xl py-2 px-3 text-[10px] text-rose-300 font-mono focus:outline-none cursor-pointer" onclick="this.select(); document.execCommand('copy');">
                                    </div>
                                    <div>
                                        <label class="text-[9px] text-white/20 uppercase tracking-widest font-black mb-1 block">Markdown</label>
                                        <input type="text" readonly value="[![Signature]({{ route('signature') }}?type=1)]({{ url('/') }})"
                                               class="w-full bg-black/40 border border-white/10 rounded-xl py-2 px-3 text-[10px] text-blue-300 font-mono focus:outline-none cursor-pointer" onclick="this.select(); document.execCommand('copy');">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] italic">Typ 2 (Kompakt)</p>
                                <div class="p-6 bg-black/20 rounded-[2rem] border border-white/5 overflow-hidden">
                                    <img src="{{ route('signature') }}?type=2&t={{ time() }}" alt="Banner Type 2" class="max-w-full h-auto rounded-xl shadow-2xl border border-white/10">
                                </div>
                                <div class="mt-3 space-y-3 px-1">
                                    <div>
                                        <label class="text-[9px] text-white/20 uppercase tracking-widest font-black mb-1 block">BBCode für Foren</label>
                                        <input type="text" readonly value="[url={{ url('/') }}][img]{{ route('signature') }}?type=2[/img][/url]"
                                               class="w-full bg-black/40 border border-white/10 rounded-xl py-2 px-3 text-[10px] text-rose-300 font-mono focus:outline-none cursor-pointer" onclick="this.select(); document.execCommand('copy');">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] italic">Typ 3 (Minimal)</p>
                                <div class="p-6 bg-black/20 rounded-[2rem] border border-white/5 overflow-hidden">
                                    <img src="{{ route('signature') }}?type=3&t={{ time() }}" alt="Banner Type 3" class="max-w-full h-auto rounded-xl shadow-2xl border border-white/10">
                                </div>
                                <div class="mt-3 space-y-3 px-1">
                                    <div>
                                        <label class="text-[9px] text-white/20 uppercase tracking-widest font-black mb-1 block">BBCode für Foren</label>
                                        <input type="text" readonly value="[url={{ url('/') }}][img]{{ route('signature') }}?type=3[/img][/url]"
                                               class="w-full bg-black/40 border border-white/10 rounded-xl py-2 px-3 text-[10px] text-rose-300 font-mono focus:outline-none cursor-pointer" onclick="this.select(); document.execCommand('copy');">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-Mail / Server Settings -->
            <div x-show="activeTab === 'mail'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    <div class="flex items-center justify-between gap-6 mb-12">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center text-white text-2xl shadow-xl shadow-rose-600/20 ring-2 ring-white/10">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-white tracking-tight uppercase">Mail-Konfiguration</h2>
                                <p class="text-sm text-white/40 font-medium tracking-wide">SMTP-Einstellungen für den E-Mail-Versand.</p>
                            </div>
                        </div>
                        <div x-data="{ sending: false, success: null, error: null }">
                            <button @click="
                                sending = true;
                                fetch('{{ route('admin.settings.test-mail') }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    sending = false;
                                    if(data.success) { success = data.message; setTimeout(() => success = null, 5000); }
                                    else { error = data.message; setTimeout(() => error = null, 5000); }
                                })
                                .catch(err => { sending = false; error = 'Fehler beim Senden.'; setTimeout(() => error = null, 5000); })
                            " type="button"
                                :disabled="sending"
                                class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all border border-white/10 flex items-center gap-3 disabled:opacity-50 shadow-lg">
                                <i class="bi bi-send" :class="sending ? 'animate-pulse' : ''"></i>
                                <span x-text="sending ? 'Sende...' : 'Test-Mail senden'"></span>
                            </button>
                            <p x-show="success" x-text="success" class="text-[10px] text-emerald-400 mt-2 font-bold text-right" style="display:none"></p>
                            <p x-show="error" x-text="error" class="text-[10px] text-rose-400 mt-2 font-bold text-right" style="display:none"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div>
                            <label for="mail_mailer" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Treiber (Mailer)</label>
                            <select name="mail_mailer" id="mail_mailer" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') == 'smtp' ? 'selected' : '' }} class="bg-zinc-900">SMTP (Empfohlen)</option>
                                <option value="log" {{ ($settings['mail_mailer'] ?? 'smtp') == 'log' ? 'selected' : '' }} class="bg-zinc-900">Log (Nur Debugging)</option>
                                <option value="sendmail" {{ ($settings['mail_mailer'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }} class="bg-zinc-900">SendMail</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-6">
                            <div class="col-span-2">
                                <label for="mail_host" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">SMTP Host</label>
                                <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" placeholder="smtp.example.com" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                            <div>
                                <label for="mail_port" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Port</label>
                                <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                        </div>
                        <div>
                            <label for="mail_username" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Benutzername</label>
                            <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div>
                            <label for="mail_password" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Passwort</label>
                            <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>
                        <div>
                            <label for="mail_encryption" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Verschlüsselung</label>
                            <select name="mail_encryption" id="mail_encryption" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                                <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }} class="bg-zinc-900">TLS (StartTLS)</option>
                                <option value="ssl" {{ ($settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }} class="bg-zinc-900">SSL</option>
                                <option value="none" {{ ($settings['mail_encryption'] ?? 'tls') == 'none' ? 'selected' : '' }} class="bg-zinc-900">Keine</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-10 pt-8 border-t border-white/5 mt-4">
                            <div>
                                <label for="mail_from_address" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Absender-Email (From)</label>
                                <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" placeholder="no-reply@deinedomain.de" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                            <div>
                                <label for="mail_from_name" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Absender-Name</label>
                                <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'MovieShelf') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Save Actions -->
            <div class="flex items-center justify-end mt-12 mb-10">
                <button type="submit" class="px-12 py-6 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-rose-600/30 flex items-center gap-4 transform hover:scale-[1.03] active:scale-[0.98]">
                    <i class="bi bi-save2 text-base"></i>
                    Konfiguration speichern
                </button>
            </div>
        </form>

        <!-- Backup Settings (Moved outside form for better download handling) -->
        <div x-show="activeTab === 'backup'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass p-8 md:p-12 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                <div class="flex items-center gap-6 mb-12">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-emerald-500/20 ring-2 ring-white/10">
                        <i class="bi bi-cloud-download-fill"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-white tracking-tight uppercase">Daten-Export & Backup</h2>
                        <p class="text-sm text-white/40 font-medium tracking-wide">Sichere deine gesamte Sammlung inklusive aller Medien.</p>
                    </div>
                </div>

                <div class="space-y-10">
                    <div class="p-8 bg-black/20 border border-white/5 rounded-[2rem] flex flex-col md:flex-row items-center gap-8">
                        <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center shrink-0 border border-emerald-500/20">
                            <i class="bi bi-archive text-emerald-400 text-3xl"></i>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-lg font-black text-white uppercase tracking-widest mb-2">Vollständiges Backup (ZIP)</h3>
                            <p class="text-xs text-white/50 leading-relaxed font-medium mb-6">
                                Dieser Export erstellt ein ZIP-Archiv, das die gesamte SQLite-Datenbank sowie alle Cover, Backdrops und Schauspieler-Bilder enthält. Je nach Größe deiner Sammlung kann dieser Vorgang einige Zeit in Anspruch nehmen.
                            </p>
                            <a href="{{ route('admin.backup.export') }}" download class="inline-flex items-center gap-3 px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20">
                                <i class="bi bi-download"></i>
                                Jetzt Exportieren
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 text-center">
                            <div class="text-rose-500 text-2xl mb-2"><i class="bi bi-database"></i></div>
                            <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Datenbank</div>
                            <div class="text-white font-bold mt-1">SQLite</div>
                        </div>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 text-center">
                            <div class="text-rose-500 text-2xl mb-2"><i class="bi bi-images"></i></div>
                            <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Medien</div>
                            <div class="text-white font-bold mt-1">Covers & Backdrops</div>
                        </div>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 text-center">
                            <div class="text-rose-500 text-2xl mb-2"><i class="bi bi-people"></i></div>
                            <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Akteure</div>
                            <div class="text-white font-bold mt-1">Profile & Fotos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>