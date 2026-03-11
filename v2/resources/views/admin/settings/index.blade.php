<x-admin-layout>
    @section('header_title', 'System-Einstellungen')

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
            @csrf

            <!-- General Settings -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-gear text-blue-400"></i>
                    Allgemeine Konfiguration
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                </div>
            </div>

            <!-- TMDb Integration -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-star-fill text-amber-400"></i>
                    TMDb Integration
                </h3>

                <div class="space-y-6">
                    <div>
                        <label for="tmdb_api_key" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">TMDb API Key (v3)</label>
                        <input type="password" name="tmdb_api_key" id="tmdb_api_key" value="{{ old('tmdb_api_key', $settings['tmdb_api_key'] ?? '') }}"
                               placeholder="1a2b3c4d..."
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        <p class="text-[10px] text-gray-500 mt-2">Wird benötigt, um Cover und Filmdetails automatisch zu laden.</p>
                    </div>

                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl">
                        <h4 class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            API Key beantragen
                        </h4>
                        <p class="text-xs text-amber-400/80 leading-relaxed">
                            Erstelle einen Account auf <a href="https://www.themoviedb.org/" target="_blank" class="underline hover:text-amber-300">themoviedb.org</a>, gehe zu den Einstellungen und fordere dort einen API-Schlüssel für Entwickler an.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Impressum Settings -->
            <div class="glass p-8 rounded-3xl border-white/5 shadow-2xl">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-info-circle text-blue-400"></i>
                    Impressum & Rechtliches
                </h3>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <textarea name="impressum_content" id="impressum_content" rows="4" 
                                  class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">{{ old('impressum_content', $settings['impressum_content'] ?? '') }}</textarea>
                        <p class="text-[10px] text-gray-500 mt-2 italic">Hier kannst du z.B. deine Addresse oder weitere rechtliche Hinweise einfügen.</p>
                    </div>

                    <div class="flex items-center gap-3 bg-white/5 p-4 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="impressum_enabled" id="impressum_enabled" value="1" {{ (old('impressum_enabled', $settings['impressum_enabled'] ?? '1') == '1') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50 transition-all">
                            <label for="impressum_enabled" class="text-sm font-bold text-gray-300 uppercase tracking-widest cursor-pointer">Impressum öffentlich anzeigen</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Actions -->
            <div class="flex items-center justify-end pt-4">
                <button type="submit" class="px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-save"></i>
                    Einstellungen speichern
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
