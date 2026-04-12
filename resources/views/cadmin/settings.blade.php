@extends('cadmin.layout')

@section('title', 'SaaS Einstellungen | MovieShelf Mastery')

@section('header_title', 'Plattform-Einstellungen')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    
    <!-- Settings Form -->

    <form action="{{ route('cadmin.settings.update') }}" method="POST" class="space-y-8" x-data="{ activeTab: 'branding' }">
        @csrf
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap items-center gap-2 p-2 glass rounded-2xl border border-white/10 mb-8">
            <button type="button" @click="activeTab = 'branding'" 
                    :class="activeTab === 'branding' ? 'bg-rose-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-brush"></i>
                Branding
            </button>
            <button type="button" @click="activeTab = 'onboarding'" 
                    :class="activeTab === 'onboarding' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-person-plus"></i>
                Onboarding
            </button>
            <button type="button" @click="activeTab = 'defaults'" 
                    :class="activeTab === 'defaults' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-gear-wide-connected"></i>
                Standards
            </button>
            <button type="button" @click="activeTab = 'mail'" 
                    :class="activeTab === 'mail' ? 'bg-sky-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-envelope-at"></i>
                E-Mail
            </button>
            <button type="button" @click="activeTab = 'legal'"
                    :class="activeTab === 'legal' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-file-earmark-text"></i>
                Rechtliches
            </button>
            <button type="button" @click="activeTab = 'announcement'"
                    :class="activeTab === 'announcement' ? 'bg-amber-500 text-black' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="bi bi-megaphone-fill"></i>
                Ankündigung
            </button>
        </div>

        <div x-show="activeTab === 'branding'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300">
            <!-- Branding Section -->
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
            <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-2xl flex items-center justify-center border border-rose-500/30">
                        <i class="bi bi-brush text-rose-500 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-tight">SaaS Branding</h2>
                        <p class="text-gray-400 text-sm font-medium">Name und Identität der Plattform</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Plattform Name</label>
                    <input type="text" name="saas_name" value="{{ $settings['saas_name'] }}" 
                           class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Support E-Mail</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] }}" 
                           class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Landing Page Headline</label>
                    <input type="text" name="saas_headline" value="{{ $settings['saas_headline'] }}" 
                           class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none">
                </div>
            </div>
        </div>
        </div>
        
        <div x-show="activeTab === 'onboarding'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300" x-cloak>
            <!-- Onboarding Section -->
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
            <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center border border-blue-500/30">
                        <i class="bi bi-person-plus text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-tight">Onboarding Flow</h2>
                        <p class="text-gray-400 text-sm font-medium">Wie neue Tenants aktiviert werden</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Aktivierungs-Modus</label>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
                                <input type="radio" name="onboarding_mode" value="manual" {{ $settings['onboarding_mode'] == 'manual' ? 'checked' : '' }} class="hidden peer">
                                <div class="w-6 h-6 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-rose-500 peer-checked:bg-rose-500 transition-all flex-shrink-0">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="ms-4">
                                    <span class="block text-sm font-bold text-white group-hover:text-rose-400 transition-colors">Manuelle Prüfung <span class="text-[10px] text-rose-500 font-black uppercase tracking-widest">(Empfohlen)</span></span>
                                    <span class="block text-xs text-gray-500 mt-0.5">Du schaltest jedes Regal selbst frei. Der Nutzer bekommt nach Aktivierung eine E-Mail.</span>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
                                <input type="radio" name="onboarding_mode" value="email" {{ $settings['onboarding_mode'] == 'email' ? 'checked' : '' }} class="hidden peer">
                                <div class="w-6 h-6 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-blue-500 peer-checked:bg-blue-500 transition-all flex-shrink-0">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="ms-4">
                                    <span class="block text-sm font-bold text-white group-hover:text-blue-400 transition-colors">E-Mail Aktivierung</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">Nutzer erhalten einen Aktivierungslink per E-Mail und schalten ihr Regal selbst frei.</span>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
                                <input type="radio" name="onboarding_mode" value="auto" {{ $settings['onboarding_mode'] == 'auto' ? 'checked' : '' }} class="hidden peer">
                                <div class="w-6 h-6 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-green-500 peer-checked:bg-green-500 transition-all flex-shrink-0">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="ms-4">
                                    <span class="block text-sm font-bold text-white group-hover:text-green-400 transition-colors">Sofort-Aktivierung</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">Tenants werden sofort nach der Registrierung freigeschaltet. Bestätigungs-E-Mail wird gesendet.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white/[0.03] rounded-3xl border border-white/10 p-6 space-y-4">
                        <h3 class="text-white font-bold flex items-center gap-2 text-sm">
                            <i class="bi bi-envelope-at text-sky-400"></i>
                            E-Mail Versand
                        </h3>
                        <ul class="space-y-2 text-xs text-gray-400 leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-rose-400 font-black mt-0.5">Manuell</span><span>→ E-Mail nach Admin-Freischaltung</span></li>
                            <li class="flex items-start gap-2"><span class="text-blue-400 font-black mt-0.5">E-Mail</span><span>→ Aktivierungslink direkt nach Registrierung</span></li>
                            <li class="flex items-start gap-2"><span class="text-green-400 font-black mt-0.5">Sofort</span><span>→ Willkommens-E-Mail mit Login-Link</span></li>
                        </ul>
                        <p class="text-[10px] text-gray-600 pt-2 border-t border-white/5">
                            Stelle sicher, dass der SMTP-Server im E-Mail Tab korrekt konfiguriert ist.
                        </p>
                    </div>

                    <div class="md:col-span-2 space-y-3 pt-6 border-t border-white/10"
                         x-data="{
                            words: {{ json_encode(array_values(array_filter(array_map('trim', explode(',', $settings['forbidden_subdomains'])))), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }},
                            input: '',
                            importText: '',
                            showImport: false,
                            importAdded: 0,
                            get csv() { return this.words.join(',') },
                            sanitize(w) { return w.toLowerCase().replace(/[^a-z0-9-]/g, '').trim(); },
                            add() {
                                const w = this.sanitize(this.input);
                                if (w.length >= 2 && !this.words.includes(w)) this.words.push(w);
                                this.input = '';
                            },
                            remove(w) { this.words = this.words.filter(x => x !== w) },
                            onKey(e) { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); this.add(); } },
                            importList() {
                                const raw = this.importText.split(/[\n,;\s]+/);
                                let added = 0;
                                raw.forEach(entry => {
                                    const w = this.sanitize(entry);
                                    if (w.length >= 2 && !this.words.includes(w)) {
                                        this.words.push(w);
                                        added++;
                                    }
                                });
                                this.words.sort();
                                this.importAdded = added;
                                this.importText = '';
                                this.showImport = false;
                            }
                         }">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 ms-1">Gesperrte Subdomains</label>
                            <div class="flex items-center gap-3">
                                <span x-show="importAdded > 0" x-text="importAdded + ' hinzugefügt'"
                                      class="text-[10px] font-black text-emerald-400 uppercase tracking-widest"
                                      x-transition></span>
                                <button type="button" @click="showImport = !showImport"
                                        :class="showImport ? 'bg-indigo-500/30 border-indigo-500/50 text-indigo-300' : 'bg-white/5 border-white/10 text-white/40 hover:text-white'"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition-all">
                                    <i class="bi bi-upload text-xs"></i> Liste importieren
                                </button>
                                <button type="button" @click="if(confirm('Alle gesperrten Subdomains löschen?')) { words = []; importAdded = 0; }"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border bg-white/5 border-white/10 text-white/20 hover:text-rose-400 hover:border-rose-500/30 text-[10px] font-black uppercase tracking-widest transition-all">
                                    <i class="bi bi-trash text-xs"></i> Alle löschen
                                </button>
                            </div>
                        </div>

                        {{-- Hidden field for form submission --}}
                        <input type="hidden" name="forbidden_subdomains" :value="csv">

                        {{-- Import panel --}}
                        <div x-show="showImport" x-transition class="p-4 rounded-xl bg-indigo-500/5 border border-indigo-500/20 space-y-3">
                            <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">
                                Liste einfügen — ein Wort pro Zeile, oder komma-/semikolon-/leerzeichengetrennt
                            </p>
                            <textarea x-model="importText" rows="6"
                                      placeholder="admin&#10;api&#10;www&#10;support&#10;mail&#10;test&#10;dev&#10;..."
                                      class="w-full bg-white/5 border border-indigo-500/30 rounded-xl px-4 py-3 text-white text-sm font-mono outline-none focus:border-indigo-500/60 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-white/20 resize-y"></textarea>
                            <div class="flex gap-2 justify-end">
                                <button type="button" @click="showImport = false; importText = ''"
                                        class="px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white/40 text-xs font-black uppercase tracking-widest hover:text-white transition-all">
                                    Abbrechen
                                </button>
                                <button type="button" @click="importList()"
                                        :disabled="importText.trim().length === 0"
                                        class="px-4 py-2 rounded-lg bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-xs font-black uppercase tracking-widest hover:bg-indigo-500/30 transition-all disabled:opacity-30">
                                    <i class="bi bi-check-lg me-1"></i> Importieren
                                </button>
                            </div>
                        </div>

                        {{-- Tag display --}}
                        <div class="min-h-[3rem] flex flex-wrap gap-2 p-3 bg-white/5 border border-white/10 rounded-xl">
                            <template x-for="w in words" :key="w">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-black uppercase tracking-widest">
                                    <span x-text="w"></span>
                                    <button type="button" @click="remove(w)"
                                            class="text-rose-400/60 hover:text-rose-300 transition-colors leading-none">
                                        <i class="bi bi-x text-sm"></i>
                                    </button>
                                </span>
                            </template>
                            <span x-show="words.length === 0" class="text-white/20 text-xs italic self-center">Keine gesperrten Subdomains</span>
                        </div>

                        {{-- Add input --}}
                        <div class="flex gap-2">
                            <input type="text" x-model="input" @keydown="onKey($event)"
                                   placeholder="Begriff eingeben + Enter"
                                   class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm font-mono outline-none focus:border-rose-500/50 focus:ring-2 focus:ring-rose-500/20 transition-all placeholder-white/20">
                            <button type="button" @click="add()"
                                    class="px-4 py-2.5 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-400 text-sm font-black hover:bg-rose-500/30 transition-all">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-600 font-medium">
                            Nur Kleinbuchstaben, Zahlen und Bindestriche · mind. 2 Zeichen · Enter oder Komma zum Hinzufügen
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div x-show="activeTab === 'defaults'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300" x-cloak>
            <!-- Default Tenant Configuration -->
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
            <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center border border-indigo-500/30">
                        <i class="bi bi-gear-wide-connected text-indigo-500 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-tight">Default Tenant Config</h2>
                        <p class="text-gray-400 text-sm font-medium">Standard-Einstellungen für neue Filmregale</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8 space-y-8">
                <!-- TMDb API Key -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Globaler TMDb API Key</label>
                    <input type="text" name="global_tmdb_key" value="{{ $settings['global_tmdb_key'] }}"
                        placeholder="z.B. eyJhbGciOiJIUzI1NiJ9..."
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/20 font-mono transition-all">
                    <p class="text-[10px] text-gray-600 mt-2 ms-1">Wird als Fallback verwendet, wenn ein Tenant keinen eigenen Key konfiguriert hat.</p>
                </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Default Layout -->
                <div class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Standard Layout</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-3 cursor-pointer glass border border-white/10 rounded-xl group hover:bg-white/5 transition-all">
                            <input type="radio" name="default_tenant_layout" value="classic" {{ $settings['default_tenant_layout'] == 'classic' ? 'checked' : '' }} class="hidden peer">
                            <div class="w-5 h-5 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all">
                                <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="ms-3 text-sm font-bold text-white">Classic</span>
                        </label>
                        <label class="relative flex items-center p-3 cursor-pointer glass border border-white/10 rounded-xl group hover:bg-white/5 transition-all">
                            <input type="radio" name="default_tenant_layout" value="streaming" {{ $settings['default_tenant_layout'] == 'streaming' ? 'checked' : '' }} class="hidden peer">
                            <div class="w-5 h-5 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all">
                                <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="ms-3 text-sm font-bold text-white">Streaming</span>
                        </label>
                    </div>
                </div>

                <!-- Default Language -->
                <div class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Standard Sprache</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-3 cursor-pointer glass border border-white/10 rounded-xl group hover:bg-white/5 transition-all">
                            <input type="radio" name="default_tenant_language" value="de" {{ $settings['default_tenant_language'] == 'de' ? 'checked' : '' }} class="hidden peer">
                            <div class="w-5 h-5 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all">
                                <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="ms-3 text-sm font-bold text-white">Deutsch</span>
                        </label>
                        <label class="relative flex items-center p-3 cursor-pointer glass border border-white/10 rounded-xl group hover:bg-white/5 transition-all">
                            <input type="radio" name="default_tenant_language" value="en" {{ $settings['default_tenant_language'] == 'en' ? 'checked' : '' }} class="hidden peer">
                            <div class="w-5 h-5 border-2 border-white/20 rounded-full flex items-center justify-center peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all">
                                <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="ms-3 text-sm font-bold text-white">English</span>
                        </label>
                    </div>
                </div>
            </div>
            </div>
        </div>
        </div>

        <div x-show="activeTab === 'mail'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300" x-cloak>
            <!-- E-Mail Service (SMTP) -->
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
                <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-sky-500/20 rounded-2xl flex items-center justify-center border border-sky-500/30">
                            <i class="bi bi-envelope-at text-sky-500 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white uppercase tracking-tight">E-Mail Service (SMTP)</h2>
                            <p class="text-gray-400 text-sm font-medium">Zentraler Mail-Versand für die gesamte Plattform</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">SMTP Host</label>
                            <input type="text" name="mail_host" value="{{ $settings['mail_host'] }}" placeholder="z.B. smtp.mailtrap.io"
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Port</label>
                            <input type="number" name="mail_port" value="{{ $settings['mail_port'] }}" placeholder="587"
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Username</label>
                            <input type="text" name="mail_username" value="{{ $settings['mail_username'] }}" 
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Password</label>
                            <input type="password" name="mail_password" value="{{ $settings['mail_password'] }}" 
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Encryption</label>
                            <select name="mail_encryption" class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none appearance-none">
                                <option value="tls" {{ $settings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS (Empfohlen)</option>
                                <option value="ssl" {{ $settings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ $settings['mail_encryption'] == 'null' ? 'selected' : '' }}>Keine</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Sender Email</label>
                            <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] }}" placeholder="noreply@movieshelf.info"
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ms-1">Sender Name</label>
                            <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] }}" placeholder="MovieShelf Magic"
                                   class="block w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500/50 text-white transition-all outline-none">
                        </div>
                    </div>

                    <div class="bg-sky-500/5 rounded-2xl border border-sky-500/10 p-4 flex items-start gap-4">
                        <i class="bi bi-info-circle text-sky-500 mt-1"></i>
                        <p class="text-[11px] text-gray-400 leading-relaxed drop-shadow-md">
                            ⚠️ **Hinweis**: Falls diese Felder leer bleiben, nutzt das System die Standardwerte aus der `.env`-Konfigurationsdatei. Achte darauf, dass dein Mail-Provider den Versand von dieser Domain erlaubt.
                        </p>
                    </div>

                    <div class="mt-4 flex flex-col items-center border-t border-white/10 pt-6" x-data="{
                        testing: false,
                        testStatus: null,
                        testMessage: '',
                        testMail() {
                            if(this.testing) return;
                            this.testing = true;
                            this.testStatus = null;
                            
                            fetch('{{ route('cadmin.settings.test-mail') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.testing = false;
                                this.testStatus = data.success ? 'success' : 'error';
                                this.testMessage = data.message;
                            })
                            .catch(error => {
                                this.testing = false;
                                this.testStatus = 'error';
                                this.testMessage = 'Ein Fehler ist aufgetreten: ' + error.message;
                            });
                        }
                    }">
                        <button type="button" @click="testMail()" :disabled="testing" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-bold text-white transition-all flex items-center gap-2">
                            <i class="bi bi-send" x-show="!testing"></i>
                            <i class="bi bi-arrow-repeat animate-spin" x-show="testing" style="display: none;"></i>
                            <span x-text="testing ? 'Sende Test-E-Mail...' : 'Verbindung Testen'">Verbindung Testen</span>
                        </button>
                        
                        <div x-show="testStatus" x-cloak class="mt-4 text-sm font-medium py-2 px-4 rounded-xl text-center"
                             :class="{
                                 'bg-green-500/10 text-green-400 border border-green-500/20': testStatus === 'success',
                                 'bg-red-500/10 text-red-400 border border-red-500/20': testStatus === 'error'
                             }">
                            <span x-text="testMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'legal'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300" x-cloak x-data="legalSettings()">
            <!-- Legal Section (Impressum) -->
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
                <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-2xl flex items-center justify-center border border-emerald-500/30">
                            <i class="bi bi-file-earmark-text text-emerald-500 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white uppercase tracking-tight">Rechtliches & Impressum</h2>
                            <p class="text-gray-400 text-sm font-medium">Verwalte hier das Impressum der SaaS-Plattform</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8 space-y-8">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Status</label>
                        <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all max-w-md">
                            <input type="hidden" name="saas_impressum_active" value="0">
                            <input type="checkbox" name="saas_impressum_active" value="1" {{ $settings['saas_impressum_active'] == '1' ? 'checked' : '' }}
                                   class="opacity-0 absolute inset-0 w-full h-full cursor-pointer z-10 peer">
                            <div class="w-12 h-6 bg-white/10 rounded-full p-1 transition-all peer-checked:bg-emerald-500">
                                <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
                            </div>
                            <span class="ms-4 text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Impressum Öffentlich sichtbar</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 ms-1">Impressum Inhalt (HTML)</label>
                        <div id="impressum-editor" class="quill-editor" style="min-height: 400px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem; color: #fff;"></div>
                        <input type="hidden" name="saas_impressum_content" id="saas_impressum_content" x-model="formData.saas_impressum_content">
                    </div>
                </div>
            </div>

        </div>

        {{-- Announcement Tab --}}
        <div x-show="activeTab === 'announcement'" class="space-y-8 animate-in fade-in zoom-in-95 duration-300" x-cloak>
            <div class="glass rounded-[2rem] border border-white/10 overflow-hidden">
                <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-500/20 rounded-2xl flex items-center justify-center border border-amber-500/30">
                            <i class="bi bi-megaphone-fill text-amber-400 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white uppercase tracking-tight">Globale Ankündigung</h2>
                            <p class="text-gray-400 text-sm font-medium">Banner wird auf allen aktiven Tenant-Seiten angezeigt</p>
                        </div>
                    </div>
                </div>
                <div class="p-8 space-y-6">

                    {{-- Active Toggle --}}
                    <input type="hidden" name="announcement_active" value="0">
                    <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all max-w-md">
                        <input type="checkbox" name="announcement_active" value="1"
                               {{ $settings['announcement_active'] == '1' ? 'checked' : '' }}
                               class="opacity-0 absolute inset-0 w-full h-full cursor-pointer z-10 peer">
                        <div class="w-12 h-6 bg-white/10 rounded-full p-1 transition-all peer-checked:bg-amber-500">
                            <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
                        </div>
                        <span class="ms-4 text-sm font-bold text-white group-hover:text-amber-400 transition-colors">Banner aktiv (auf allen Tenant-Seiten sichtbar)</span>
                    </label>

                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-3 ms-1">Typ</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['info' => ['label' => 'Info', 'color' => 'bg-indigo-500/20 border-indigo-500/40 text-indigo-300'], 'warning' => ['label' => 'Warnung', 'color' => 'bg-amber-500/20 border-amber-500/40 text-amber-300'], 'critical' => ['label' => 'Kritisch', 'color' => 'bg-rose-500/20 border-rose-500/40 text-rose-300']] as $val => $opt)
                            <label class="flex items-center gap-2 px-4 py-2 rounded-xl border cursor-pointer {{ $opt['color'] }} {{ $settings['announcement_type'] === $val ? 'ring-2 ring-white/20' : '' }}">
                                <input type="radio" name="announcement_type" value="{{ $val }}"
                                       {{ $settings['announcement_type'] === $val ? 'checked' : '' }}
                                       class="sr-only">
                                <span class="text-xs font-black uppercase tracking-widest">{{ $opt['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Text --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-3 ms-1">Nachricht</label>
                        <input type="text" name="announcement_text"
                               value="{{ $settings['announcement_text'] }}"
                               maxlength="500"
                               placeholder="z.B. Wartungsarbeiten am 15.01 von 02:00–04:00 Uhr."
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm outline-none focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/20 transition-all placeholder-white/20">
                        <p class="text-[10px] text-gray-600 font-medium mt-2">Max. 500 Zeichen · Nutzer können den Banner einmalig schließen (wird per localStorage gemerkt)</p>
                    </div>

                    {{-- Preview --}}
                    @if($settings['announcement_text'])
                    <div class="space-y-2">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500 ms-1">Vorschau</p>
                        @php
                            $previewColors = match($settings['announcement_type']) {
                                'warning'  => 'bg-amber-500 text-black',
                                'critical' => 'bg-rose-600 text-white',
                                default    => 'bg-indigo-500 text-white',
                            };
                            $previewIcon = match($settings['announcement_type']) {
                                'warning'  => 'bi-exclamation-triangle-fill',
                                'critical' => 'bi-exclamation-octagon-fill',
                                default    => 'bi-info-circle-fill',
                            };
                        @endphp
                        <div class="{{ $previewColors }} flex items-center justify-between gap-4 px-6 py-2.5 rounded-xl text-xs font-bold">
                            <div class="flex items-center gap-2">
                                <i class="bi {{ $previewIcon }}"></i>
                                <span>{{ $settings['announcement_text'] }}</span>
                            </div>
                            <i class="bi bi-x-lg opacity-60"></i>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-12 py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-rose-900/40 transition-all transform active:scale-95 flex items-center gap-3">
                <i class="bi bi-save2-fill"></i>
                Einstellungen Speichern
            </button>
        </div>
    </form>
</div>
@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border: 1px solid rgba(255,255,255,0.1) !important; background: rgba(5,5,5,0.8) !important; border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 15px !important; }
    .ql-container.ql-snow { border: 1px solid rgba(255,255,255,0.1) !important; border-top: none !important; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; font-family: 'Inter', sans-serif !important; font-size: 0.95rem !important; }
    .ql-editor { color: #fff !important; min-height: 300px; line-height: 1.6 !important; }
    .ql-snow .ql-stroke { stroke: #aaa !important; }
    .ql-snow .ql-fill { fill: #aaa !important; }
    .ql-snow .ql-picker { color: #aaa !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    function legalSettings() {
        return {
            initialized: false,
            formData: {
                saas_impressum_content: {!! json_encode($settings['saas_impressum_content']) !!}
            },
            init() {
                if (this.initialized) return;
                this.initialized = true;

                const setup = () => {
                    const editorContainer = document.querySelector('#impressum-editor');
                    if (!editorContainer || editorContainer.classList.contains('ql-container')) return;

                    const quill = new Quill('#impressum-editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'link'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['clean']
                            ]
                        },
                        placeholder: 'Impressum Text hier verfassen...'
                    });

                    if (this.formData.saas_impressum_content) {
                        quill.root.innerHTML = this.formData.saas_impressum_content;
                    }

                    quill.on('text-change', () => {
                        this.formData.saas_impressum_content = quill.root.innerHTML;
                    });
                };

                if (typeof Quill === 'undefined') {
                    const interval = setInterval(() => {
                        if (typeof Quill !== 'undefined') {
                            clearInterval(interval);
                            setup();
                        }
                    }, 50);
                } else {
                    this.$nextTick(() => setup());
                }
            }
        }
    }
</script>
@endpush
@endsection
