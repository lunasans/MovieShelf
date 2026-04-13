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
                                <input type="checkbox" name="public_collection_enabled" id="public_collection_enabled" value="1" {{ (old('public_collection_enabled', $settings['public_collection_enabled'] ?? '0') == '1') ? 'checked' : '' }} class="w-6 h-6 rounded-lg border-white/10 bg-white/5 text-rose-600 focus:ring-rose-500/50 transition-all cursor-pointer">
                                <label for="public_collection_enabled" class="flex-1 cursor-pointer">
                                    <span class="block text-sm font-black text-white uppercase tracking-widest">Öffentliche Sammlung aktivieren</span>
                                    <span class="text-[10px] text-white/30 font-medium italic mt-1 block tracking-wide">
                                        Macht deine Filmsammlung unter
                                        <code class="text-white/50 bg-white/5 px-1 rounded">/kollektion</code>
                                        für jeden ohne Login sichtbar.
                                    </span>
                                </label>
                                @if(($settings['public_collection_enabled'] ?? '0') == '1')
                                    <a href="{{ route('collection.index') }}" target="_blank"
                                       class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-blue-500/20 transition-all">
                                        <i class="bi bi-box-arrow-up-right"></i> Öffnen
                                    </a>
                                @endif
                            </div>
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
                    @if(Route::has('signature'))
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
                    @endif
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
    </div>
</x-admin-layout>