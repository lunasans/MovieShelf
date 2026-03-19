<x-admin-layout>
    @section('header_title', 'System-Einstellungen')

    <div class="max-w-5xl mx-auto" x-data="{ activeTab: 'general' }">
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 mb-8 bg-white/5 p-2 rounded-2xl border border-white/10 backdrop-blur-md">
            <button @click="activeTab = 'general'" 
                    :class="activeTab === 'general' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">
                <i class="bi bi-gear"></i>
                Allgemein
            </button>
            <button @click="activeTab = 'tmdb'" 
                    :class="activeTab === 'tmdb' ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">
                <i class="bi bi-star-fill"></i>
                TMDb
            </button>
            <button @click="activeTab = 'legal'" 
                    :class="activeTab === 'legal' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">
                <i class="bi bi-info-circle"></i>
                Rechtliches
            </button>
            <button @click="activeTab = 'signature'" 
                    :class="activeTab === 'signature' ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">
                <i class="bi bi-card-image"></i>
                Signaturbanner
            </button>
            <button @click="activeTab = 'mail'" 
                    :class="activeTab === 'mail' ? 'bg-rose-600 text-white shadow-lg shadow-rose-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">
                <i class="bi bi-envelope"></i>
                E-Mail / Server
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- General Settings -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 rounded-3xl border-white/10 shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl shadow-lg shadow-blue-500/20">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white uppercase tracking-wider">Allgemeine Konfiguration</h2>
                            <p class="text-sm text-gray-400">Grundlegende Einstellungen für deine Filmsammlung.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label for="site_title" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Website Titel</label>
                            <input type="text" name="site_title" id="site_title" value="{{ old('site_title', $settings['site_title'] ?? 'MovieShelf') }}" required
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        </div>

                        <div>
                            <label for="items_per_page" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Filme pro Seite</label>
                            <input type="number" name="items_per_page" id="items_per_page" value="{{ old('items_per_page', $settings['items_per_page'] ?? '20') }}" required min="5" max="100"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                            <p class="text-[10px] text-gray-500 mt-2">Anzahl der Filme in der Listenansicht.</p>
                        </div>

                        <div>
                            <label for="latest_films_count" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Anzahl neueste Filme</label>
                            <input type="number" name="latest_films_count" id="latest_films_count" value="{{ old('latest_films_count', $settings['latest_films_count'] ?? '15') }}" required min="5" max="50"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                            <p class="text-[10px] text-gray-500 mt-2">Anzahl der "Neu eingetroffen" Karten im Detail-Panel.</p>
                        </div>

                        <div>
                            <label for="default_view_mode" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Standard-Ansicht</label>
                            <select name="default_view_mode" id="default_view_mode" required
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                                <option value="grid" {{ (old('default_view_mode', $settings['default_view_mode'] ?? 'grid') == 'grid') ? 'selected' : '' }} class="bg-gray-900">Grid (Kacheln)</option>
                                <option value="list" {{ (old('default_view_mode', $settings['default_view_mode'] ?? 'grid') == 'list') ? 'selected' : '' }} class="bg-gray-900">Liste</option>
                            </select>
                        </div>

                        <div>
                            <label for="theme" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Standard-Theme</label>
                            <select name="theme" id="theme" required
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                                <option value="default" {{ (old('theme', $settings['theme'] ?? 'default') == 'default') ? 'selected' : '' }} class="bg-gray-900">Standard (Inter)</option>
                                <option value="dark" {{ (old('theme', $settings['theme'] ?? 'default') == 'dark') ? 'selected' : '' }} class="bg-gray-900">Dark</option>
                                <option value="blue" {{ (old('theme', $settings['theme'] ?? 'default') == 'blue') ? 'selected' : '' }} class="bg-gray-900">Blue Ocean</option>
                                <option value="green" {{ (old('theme', $settings['theme'] ?? 'default') == 'green') ? 'selected' : '' }} class="bg-gray-900">Green Nature</option>
                                <option value="red" {{ (old('theme', $settings['theme'] ?? 'default') == 'red') ? 'selected' : '' }} class="bg-gray-900">Red Velocity</option>
                                <option value="purple" {{ (old('theme', $settings['theme'] ?? 'default') == 'purple') ? 'selected' : '' }} class="bg-gray-900">Purple Night</option>
                                <option value="halloween" {{ (old('theme', $settings['theme'] ?? 'default') == 'halloween') ? 'selected' : '' }} class="bg-gray-900">Halloween</option>
                                <option value="christmas" {{ (old('theme', $settings['theme'] ?? 'default') == 'christmas') ? 'selected' : '' }} class="bg-gray-900">Christmas</option>
                                <option value="summer" {{ (old('theme', $settings['theme'] ?? 'default') == 'summer') ? 'selected' : '' }} class="bg-gray-900">Summer Breeze</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 border-t border-white/5 pt-8 mt-4">
                            <div class="flex items-center gap-3 bg-white/5 p-5 rounded-2xl border border-white/10">
                                <input type="checkbox" name="migration_enabled" id="migration_enabled" value="1" {{ (old('migration_enabled', $settings['migration_enabled'] ?? '1') == '1') ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50 transition-all">
                                <label for="migration_enabled" class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Daten-Migration (v1) aktivieren</label>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-2 px-2 italic">Wenn deaktiviert, wird der Menüpunkt „Daten Migration“ in der Sidebar ausgeblendet.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TMDb Integration -->
            <div x-show="activeTab === 'tmdb'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 rounded-3xl border-white/10 shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-xl shadow-lg shadow-amber-500/20">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white uppercase tracking-wider">TMDb Integration</h2>
                            <p class="text-sm text-gray-400">Automatische Filminformationen von TheMovieDatabase.</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <label for="tmdb_api_key" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">TMDb API Key (v3)</label>
                            <input type="password" name="tmdb_api_key" id="tmdb_api_key" value="{{ old('tmdb_api_key', $settings['tmdb_api_key'] ?? '') }}"
                                   placeholder="1a2b3c4d..."
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-amber-500/50 transition-all">
                        </div>

                        <div class="p-6 bg-amber-500/10 border border-amber-500/20 rounded-3xl">
                            <h4 class="text-sm font-bold text-amber-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="bi bi-info-circle"></i>
                                API Key beantragen
                            </h4>
                            <p class="text-xs text-amber-400/80 leading-relaxed">
                                Erstelle einen Account auf <a href="https://www.themoviedb.org/" target="_blank" class="underline hover:text-amber-300 font-bold">themoviedb.org</a>, gehe zu den Einstellungen und fordere dort einen API-Schlüssel für Entwickler an. Ohne diesen Schlüssel können keine Cover oder Filmdetails automatisch geladen werden.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impressum Settings -->
            <div x-show="activeTab === 'legal'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 rounded-3xl border-white/10 shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xl shadow-lg shadow-blue-500/20">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white uppercase tracking-wider">Impressum & Rechtliches</h2>
                            <p class="text-sm text-gray-400">Rechtliche Hinweise und Betreiberangaben.</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="impressum_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Name / Betreiber</label>
                                <input type="text" name="impressum_name" id="impressum_name" value="{{ old('impressum_name', $settings['impressum_name'] ?? '') }}"
                                       placeholder="Max Mustermann"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                            </div>
                            <div>
                                <label for="impressum_email" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Kontakt E-Mail</label>
                                <input type="email" name="impressum_email" id="impressum_email" value="{{ old('impressum_email', $settings['impressum_email'] ?? '') }}"
                                       placeholder="kontakt@example.com"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="impressum_content" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Zusätzlicher Text (HTML erlaubt)</label>
                            <textarea name="impressum_content" id="impressum_content" rows="6" 
                                      class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">{{ old('impressum_content', $settings['impressum_content'] ?? '') }}</textarea>
                            <p class="text-[10px] text-gray-500 mt-2 italic">Hier kannst du z.B. deine Adresse oder weitere rechtliche Hinweise einfügen.</p>
                        </div>

                        <div class="flex items-center gap-3 bg-white/5 p-5 rounded-2xl border border-white/10">
                            <input type="checkbox" name="impressum_enabled" id="impressum_enabled" value="1" {{ (old('impressum_enabled', $settings['impressum_enabled'] ?? '1') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50 transition-all">
                            <label for="impressum_enabled" class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Impressum öffentlich anzeigen</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatur-Banner -->
            <div x-show="activeTab === 'signature'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 rounded-3xl border-white/10 shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xl shadow-lg shadow-purple-500/20">
                            <i class="bi bi-card-image"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white uppercase tracking-wider">Signatur-Banner</h2>
                            <p class="text-sm text-gray-400">Konfiguration der dynamischen Foren-Banner.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div class="flex items-center gap-3 bg-white/5 p-5 rounded-2xl border border-white/10 h-fit">
                            <input type="checkbox" name="signature_enabled" id="signature_enabled" value="1" {{ (old('signature_enabled', $settings['signature_enabled'] ?? '1') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500/50 transition-all">
                            <label for="signature_enabled" class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Banner aktivieren</label>
                        </div>

                        <div>
                            <label for="signature_film_count" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Anzahl der Filme</label>
                            <input type="number" name="signature_film_count" id="signature_film_count" value="{{ old('signature_film_count', $settings['signature_film_count'] ?? '10') }}" min="1" max="20"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-purple-500/50 transition-all">
                            <p class="text-[10px] text-gray-500 mt-2">Max. 20 Filme gleichzeitig.</p>
                        </div>

                        <div>
                            <label for="signature_film_source" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Film-Quelle</label>
                            <select name="signature_film_source" id="signature_film_source"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-purple-500/50 transition-all appearance-none">
                                <option value="newest" {{ ($settings['signature_film_source'] ?? 'newest') == 'newest' ? 'selected' : '' }} class="bg-gray-900">Neueste (Hinzugefügt)</option>
                                <option value="newest_release" {{ ($settings['signature_film_source'] ?? 'newest_release') == 'newest_release' ? 'selected' : '' }} class="bg-gray-900">Neueste (Erscheinungsjahr)</option>
                                <option value="random" {{ ($settings['signature_film_source'] ?? 'random') == 'random' ? 'selected' : '' }} class="bg-gray-900">Zufällig</option>
                            </select>
                        </div>

                        <div>
                            <label for="signature_cache_time" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Cache-Zeit (Sekunden)</label>
                            <input type="number" name="signature_cache_time" id="signature_cache_time" value="{{ old('signature_cache_time', $settings['signature_cache_time'] ?? '3600') }}"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-purple-500/50 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 border-t border-white/5 pt-8">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="signature_show_title" id="signature_show_title" value="1" {{ (old('signature_show_title', $settings['signature_show_title'] ?? '1') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500/50 transition-all">
                            <span class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Titel anzeigen</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="signature_show_year" id="signature_show_year" value="1" {{ (old('signature_show_year', $settings['signature_show_year'] ?? '1') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500/50 transition-all">
                            <span class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Jahr anzeigen</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="signature_show_rating" id="signature_show_rating" value="1" {{ (old('signature_show_rating', $settings['signature_show_rating'] ?? '0') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500/50 transition-all">
                            <span class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Bewertung anzeigen</span>
                        </label>
                    </div>

                    <!-- Banner Preview -->
                    <div class="mt-12 space-y-8">
                        <div>
                            <h3 class="text-xs font-black text-white/40 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="bi bi-eye"></i> Live-Vorschau
                            </h3>
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <p class="text-[10px] text-white/30 uppercase tracking-widest font-black">Typ 1 (Klassisch)</p>
                                    <div class="p-4 bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                                        <img src="{{ route('signature') }}?type=1&t={{ time() }}" alt="Banner Type 1" class="max-w-full h-auto rounded-lg shadow-2xl">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-[10px] text-white/30 uppercase tracking-widest font-black">Typ 2 (Kompakt)</p>
                                    <div class="p-4 bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                                        <img src="{{ route('signature') }}?type=2&t={{ time() }}" alt="Banner Type 2" class="max-w-full h-auto rounded-lg shadow-2xl">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-[10px] text-white/30 uppercase tracking-widest font-black">Typ 3 (Minimal)</p>
                                    <div class="p-4 bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                                        <img src="{{ route('signature') }}?type=3&t={{ time() }}" alt="Banner Type 3" class="max-w-full h-auto rounded-lg shadow-2xl">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-purple-500/10 border border-purple-500/20 rounded-3xl">
                            <h4 class="text-sm font-bold text-purple-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="bi bi-link-45deg"></i>
                                Einbindungs-Link
                            </h4>
                            <div class="bg-black/40 p-4 rounded-xl font-mono text-[11px] text-purple-300 break-all select-all">
                                {{ url('/signature') }}?type=1
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Mail-Server -->
            <div x-show="activeTab === 'mail'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass p-8 rounded-3xl border-white/10 shadow-2xl">
                    <div class="flex items-center justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white text-xl shadow-lg shadow-rose-500/20">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white uppercase tracking-wider">Mail-Konfiguration</h2>
                                <p class="text-sm text-gray-400">SMTP-Einstellungen für den E-Mail-Versand.</p>
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
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-bold text-xs transition-all border border-white/10 flex items-center gap-2 disabled:opacity-50">
                                <i class="bi bi-send" :class="sending ? 'animate-pulse' : ''"></i>
                                <span x-text="sending ? 'Sende...' : 'Test-Mail senden'"></span>
                            </button>
                            <p x-show="success" x-text="success" class="text-[10px] text-emerald-400 mt-2 font-bold text-right" style="display:none"></p>
                            <p x-show="error" x-text="error" class="text-[10px] text-rose-400 mt-2 font-bold text-right" style="display:none"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="mail_mailer" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Treiber (Mailer)</label>
                            <select name="mail_mailer" id="mail_mailer" required
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none">
                                <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') == 'smtp' ? 'selected' : '' }} class="bg-gray-900">SMTP (Empfohlen)</option>
                                <option value="log" {{ ($settings['mail_mailer'] ?? 'smtp') == 'log' ? 'selected' : '' }} class="bg-gray-900">Log (Nur Debugging)</option>
                                <option value="sendmail" {{ ($settings['mail_mailer'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }} class="bg-gray-900">SendMail</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label for="mail_host" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">SMTP Host</label>
                                <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"
                                       placeholder="smtp.example.com"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                            <div>
                                <label for="mail_port" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Port</label>
                                <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="mail_username" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Benutzername</label>
                            <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>

                        <div>
                            <label for="mail_password" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Passwort</label>
                            <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>

                        <div>
                            <label for="mail_encryption" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Verschlüsselung</label>
                            <select name="mail_encryption" id="mail_encryption"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none">
                                <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }} class="bg-gray-900">TLS (StartTLS)</option>
                                <option value="ssl" {{ ($settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }} class="bg-gray-900">SSL</option>
                                <option value="none" {{ ($settings['mail_encryption'] ?? 'tls') == 'none' ? 'selected' : '' }} class="bg-gray-900">Keine</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-white/5 mt-4">
                            <div>
                                <label for="mail_from_address" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Absender-Email (From)</label>
                                <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}"
                                       placeholder="no-reply@deinedomain.de"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>

                            <div>
                                <label for="mail_from_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Absender-Name</label>
                                <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'MovieShelf') }}"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Save Actions -->
            <div class="flex items-center justify-end mt-8">
                <button type="submit" class="px-10 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-3xl font-bold text-sm transition-all shadow-xl shadow-blue-500/20 flex items-center gap-2 transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="bi bi-save"></i>
                    Einstellungen speichern
                </button>
            </div>
        </form>
    </div>
    </div>
</x-admin-layout>
