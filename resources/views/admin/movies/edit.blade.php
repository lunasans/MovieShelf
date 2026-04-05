<x-admin-layout>
    @section('header_title', 'Film bearbeiten')
    
    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow {
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-top-left-radius: 1.5rem;
            border-top-right-radius: 1.5rem;
            padding: 15px 25px !important;
        }
        .ql-container.ql-snow {
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-top: none !important;
            background: rgba(15, 23, 42, 0.5) !important;
            border-bottom-left-radius: 1.5rem;
            border-bottom-right-radius: 1.5rem;
            font-family: inherit !important;
            font-size: 0.95rem !important;
        }
        .ql-editor {
            min-height: 250px;
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 25px !important;
            line-height: 1.8 !important;
        }
        /* Toolbar Icon Colors */
        .ql-snow .ql-stroke { stroke: rgba(255, 255, 255, 0.5) !important; }
        .ql-snow .ql-fill { fill: rgba(255, 255, 255, 0.5) !important; }
        .ql-snow .ql-picker { color: rgba(255, 255, 255, 0.5) !important; }
        
        /* Active States - Red Overhaul */
        .ql-snow.ql-toolbar button:hover .ql-stroke,
        .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #e11d48 !important; }
        .ql-snow.ql-toolbar button:hover .ql-fill,
        .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #e11d48 !important; }
        
        .ql-editor.ql-blank::before {
            color: rgba(255, 255, 255, 0.2) !important;
            font-style: italic !important;
        }

        /* Custom Actor Button - Red */
        .ql-actor::after {
            content: "\F4D1";
            font-family: "bootstrap-icons" !important;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }
        .ql-actor:hover::after {
            color: #e11d48;
        }
    </style>
    @endpush

    <div class="max-w-5xl mx-auto" x-data="tmdbSearch()">
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <a href="{{ route('admin.movies.index') }}" class="text-xs font-black text-white/30 hover:text-rose-400 uppercase tracking-[0.2em] transition-all flex items-center gap-2 group">
                <i class="bi bi-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
                Zurück zur Liste
            </a>
            <button @click="openModal()" type="button" class="px-8 py-4 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center gap-3">
                <i class="bi bi-search"></i>
                Daten von TMDb laden
            </button>
        </div>

        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
            @csrf
            @method('PUT')

            <input type="hidden" name="tmdb_id" x-model="formData.tmdb_id">
            <input type="hidden" name="cover_id" x-model="formData.cover_id">
            <input type="hidden" name="backdrop_id" x-model="formData.backdrop_id">

            <!-- General Info -->
            <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-10 flex items-center gap-3">
                    <i class="bi bi-info-circle-fill text-rose-500"></i>
                    Stammdaten
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Originaltitel / Titel *</label>
                        <input type="text" name="title" id="title" x-model="formData.title" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white text-lg font-black focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                        @error('title') <p class="text-rose-400 text-[10px] mt-2 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="year" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Erscheinungsjahr *</label>
                        <input type="number" name="year" id="year" x-model="formData.year" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all font-bold">
                    </div>

                    <div>
                        <label for="collection_type" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Archiv-Status *</label>
                        <select name="collection_type" id="collection_type" x-model="formData.collection_type" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer font-bold">
                            <option value="Owned">Besitzt (Physisch)</option>
                            <option value="Serie">Serie (Episodisch)</option>
                            <option value="Stream">Streaming / Digital</option>
                        </select>
                    </div>

                    <div>
                        <label for="genre" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Genre / Kategorien</label>
                        <input type="text" name="genre" id="genre" x-model="formData.genre"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                    </div>

                    <div>
                        <label for="runtime" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Laufzeit (Minuten)</label>
                        <input type="number" name="runtime" id="runtime" x-model="formData.runtime"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                    </div>

                    <div>
                        <label for="rating" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">TMDb Bewertung</label>
                        <input type="number" step="0.1" min="0" name="rating" id="rating" x-model="formData.rating"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                    </div>

                    <div>
                        <label for="rating_age" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Jugendschutz (FSK)</label>
                        <select name="rating_age" id="rating_age" x-model="formData.rating_age"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none cursor-pointer">
                            <option value="">Nicht geprüft</option>
                            @foreach([0, 6, 12, 16, 18] as $age)
                                <option value="{{ $age }}">Ab {{ $age }} Jahren</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="created_at" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Hinzugefügt am:</label>
                        <input type="datetime-local" name="created_at" id="created_at" x-model="formData.created_at"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all font-bold">
                    </div>
                </div>
            </div>

            <!-- Media Section -->
            <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl">
                <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-10 flex items-center gap-3">
                    <i class="bi bi-image-fill text-rose-500"></i>
                    Visuelle Medien
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Cover -->
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-2 px-1">Filmplakat (Cover)</label>
                        <div class="relative group aspect-[2/3] rounded-[2rem] overflow-hidden bg-white/5 border border-white/10 flex flex-col items-center justify-center cursor-pointer hover:border-rose-500/40 transition-all shadow-xl"
                             @click="$refs.coverInput.click()">
                            
                            <template x-if="coverPreview || formData.cover_id">
                                <img :src="coverPreview || getImageUrl(formData.cover_id, 'cover')" 
                                     class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                                     :class="coverPreview ? 'opacity-100' : 'opacity-60'">
                            </template>

                            <div class="relative z-10 flex flex-col items-center gap-4 p-8 text-center">
                                <div class="w-16 h-16 rounded-full bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                                    <i class="bi bi-cloud-arrow-up text-2xl"></i>
                                </div>
                                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">Klicke zum Bearbeiten</span>
                            </div>

                            <input type="file" name="cover_upload" x-ref="coverInput" class="hidden" accept="image/*" @change="handleFileChange($event, 'cover')">
                        </div>
                    </div>

                    <!-- Backdrop -->
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-2 px-1">Hintergrundbild (Backdrop)</label>
                        <div class="relative group aspect-video rounded-[2rem] overflow-hidden bg-white/5 border border-white/10 flex flex-col items-center justify-center cursor-pointer hover:border-rose-500/40 transition-all shadow-xl"
                             @click="$refs.backdropInput.click()">
                            
                            <template x-if="backdropPreview || formData.backdrop_id">
                                <img :src="backdropPreview || getImageUrl(formData.backdrop_id, 'backdrop')" 
                                     class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                                     :class="backdropPreview ? 'opacity-100' : 'opacity-60'">
                            </template>

                            <div class="relative z-10 flex flex-col items-center gap-4 p-8 text-center">
                                <div class="w-16 h-16 rounded-full bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                                    <i class="bi bi-image text-2xl"></i>
                                </div>
                                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">Klicke zum Bearbeiten</span>
                            </div>

                            <input type="file" name="backdrop_upload" x-ref="backdropInput" class="hidden" accept="image/*" @change="handleFileChange($event, 'backdrop')">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storyline / Overview Section -->
            <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden" x-init="initQuill()">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-10 flex items-center gap-3">
                    <i class="bi bi-card-text text-rose-500"></i>
                    Handlung & Details
                </h3>

                <div class="space-y-8">
                    <div>
                        <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Filmhandlung / Storyline</label>
                        <div id="overview-editor"></div>
                        <input type="hidden" name="overview" x-model="formData.overview">
                    </div>

                    <div>
                        <label for="trailer_url" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Trailer URL (YouTube)</label>
                        <input type="url" name="trailer_url" id="trailer_url" x-model="formData.trailer_url"
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                    </div>
                </div>
            </div>

            <!-- Actors Section -->
            <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden" x-data="actorManagement()">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i class="bi bi-people-fill text-rose-500"></i>
                        Darsteller & Rollen
                    </h3>
                    <div class="relative w-64">
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchActors()" 
                               placeholder="Darsteller suchen..."
                               class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-4 text-xs text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="searchResults.length > 0" 
                             class="absolute z-50 left-0 right-0 mt-2 bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto custom-scrollbar"
                             x-transition @click.away="searchResults = []">
                            <template x-for="actor in searchResults" :key="actor.id">
                                <div @click="addActor(actor)" class="p-3 hover:bg-white/5 cursor-pointer flex items-center gap-3 border-b border-white/5 last:border-0 group">
                                    <div class="w-8 h-10 rounded-md bg-white/5 overflow-hidden flex-shrink-0">
                                        <img :src="actor.profile_path ? (actor.profile_path.startsWith('http') ? actor.profile_path : '/storage/'+actor.profile_path) : 'https://ui-avatars.com/api/?name='+actor.first_name+'+'+actor.last_name+'&background=random'" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-[10px] font-bold text-white group-hover:text-rose-400" x-text="actor.first_name + ' ' + actor.last_name"></div>
                                        <div class="text-[8px] text-white/30 uppercase" x-text="actor.nationality || 'Unbekannt'"></div>
                                    </div>
                                    <i class="bi bi-plus-lg text-rose-500 opacity-0 group-hover:opacity-100 transition-all"></i>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <template x-for="(actor, index) in actors" :key="actor.id">
                        <div class="flex items-center gap-6 p-4 rounded-3xl bg-white/[0.02] border border-white/5 group hover:border-white/10 transition-all">
                            <!-- Hidden Inputs for Form Submission -->
                            <input type="hidden" :name="'actors['+index+'][id]'" :value="actor.id">
                            
                            <div class="w-12 h-16 rounded-xl bg-white/5 overflow-hidden flex-shrink-0 shadow-lg group-hover:scale-105 transition-transform">
                                <img :src="actor.profile_path ? (actor.profile_path.startsWith('http') ? actor.profile_path : '/storage/'+actor.profile_path) : 'https://ui-avatars.com/api/?name='+actor.first_name+'+'+actor.last_name+'&background=random'" 
                                     class="w-full h-full object-cover">
                            </div>
                            
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <div class="md:col-span-1">
                                    <div class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1 italic">Name</div>
                                    <div class="text-xs font-bold text-white" x-text="actor.first_name + ' ' + actor.last_name"></div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-1">Rolle / Charakter</div>
                                    <input type="text" :name="'actors['+index+'][role]'" x-model="actor.pivot.role"
                                           class="w-full bg-black/20 border border-white/5 rounded-xl py-2 px-4 text-xs text-white focus:outline-none focus:border-rose-500/50 transition-all font-medium">
                                </div>
                                <div class="md:col-span-1 flex items-center justify-end gap-4">
                                    <div class="text-right">
                                        <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-1">Hauptrolle</div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :name="'actors['+index+'][is_main_role]'" value="1" x-model="actor.pivot.is_main_role" class="sr-only peer">
                                            <div class="w-9 h-5 bg-white/5 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white/20 after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rose-500 peer-checked:after:bg-white shadow-inner"></div>
                                        </label>
                                    </div>
                                    <button type="button" @click="removeActor(index)" class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-white/20 hover:bg-rose-500/20 hover:text-rose-500 transition-all">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Sort Order (Hidden) -->
                            <input type="hidden" :name="'actors['+index+'][sort_order]'" :value="index">
                        </div>
                    </template>
                    
                    <div x-show="actors.length === 0" class="text-center py-12 rounded-[2rem] border-2 border-dashed border-white/5 text-white/20">
                        <i class="bi bi-person-slash text-4xl block mb-4"></i>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em]">Keine Darsteller zugewiesen</p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-6 pt-6 mb-12">
                <a href="{{ route('admin.movies.index') }}" class="px-8 py-4 text-[10px] font-black text-white/20 hover:text-white uppercase tracking-widest transition-all">
                    Verwerfen
                </a>
                <button type="submit" class="px-12 py-5 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-rose-600/30 flex items-center gap-4 transform hover:scale-[1.03] active:scale-[0.98]">
                    <i class="bi bi-save2 text-base"></i>
                    Änderungen sichern
                </button>
            </div>
        </form>

        <!-- TMDb Search Modal -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/95 backdrop-blur-xl"
             style="display: none;"
             x-cloak>
            <div class="glass w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-3xl border border-white/10 max-h-[85vh] flex flex-col" @click.away="showModal = false">
                <div class="p-10 border-b border-white/10 flex justify-between items-center bg-white/[0.02]">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight">TMDb Sync</h2>
                        <p class="text-rose-500/60 text-[10px] font-black uppercase tracking-[0.3em] mt-2">Datenbank durchsuchen</p>
                    </div>
                    <button @click="showModal = false" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/20 hover:text-white transition-all">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>

                <div class="p-10 pb-4">
                    <div class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.500ms="search()"
                            placeholder="Filmtitel oder URL..."
                            class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] px-8 py-5 text-white placeholder-white/20 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500/50 transition-all font-bold"
                        >
                        <div class="absolute right-8 top-1/2 -translate-y-1/2 flex items-center gap-4">
                            <div x-show="loading" class="animate-spin h-6 w-6 border-2 border-rose-500 border-t-transparent rounded-full"></div>
                            <i class="bi bi-search text-white/10 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-y-auto p-10 pt-0 flex-1 custom-scrollbar">
                    <div class="grid grid-cols-1 gap-5">
                        <template x-for="item in results" :key="item.id">
                            <div @click="selectItem(item)" class="group flex items-center gap-6 p-6 rounded-[2rem] border border-white/5 bg-white/[0.02] hover:bg-white/[0.08] hover:border-rose-500/40 transition-all cursor-pointer">
                                <div class="w-20 h-28 bg-gray-800 rounded-xl overflow-hidden flex-shrink-0 border border-white/5 shadow-xl">
                                    <template x-if="item.poster_path">
                                        <img :src="'https://image.tmdb.org/t/p/w92' + item.poster_path" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white font-black text-lg group-hover:text-rose-400 transition-colors" x-text="item.title || item.name"></div>
                                    <div class="text-rose-500/60 text-[10px] font-black uppercase tracking-widest mt-1" x-text="(item.release_date || item.first_air_date || '').substring(0, 4)"></div>
                                    <div class="text-white/20 text-xs mt-3 line-clamp-2 italic leading-relaxed" x-text="item.overview"></div>
                                </div>
                                <div class="text-rose-500 opacity-0 group-hover:opacity-100 transition-all transform scale-50 group-hover:scale-100">
                                    <i class="bi bi-check2-circle text-3xl"></i>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        function tmdbSearch() {
            return {
                showModal: false,
                quill: null,
                searchQuery: {!! json_encode($movie->title) !!},
                results: [],
                loading: false,
                formData: {
                    title: {!! json_encode(old('title', $movie->title)) !!},
                    year: {!! json_encode(old('year', $movie->year)) !!},
                    collection_type: {!! json_encode(old('collection_type', $movie->collection_type)) !!},
                    genre: {!! json_encode(old('genre', $movie->genre)) !!},
                    runtime: {!! json_encode(old('runtime', $movie->runtime)) !!},
                    rating: {!! json_encode(old('rating', $movie->rating ? round($movie->rating, 1) : null)) !!},
                    rating_age: {!! json_encode(old('rating_age', $movie->rating_age)) !!},
                    trailer_url: {!! json_encode(old('trailer_url', $movie->trailer_url)) !!},
                    overview: {!! json_encode(old('overview', $movie->overview)) !!},
                    tmdb_id: {!! json_encode(old('tmdb_id', $movie->tmdb_id)) !!},
                    cover_id: {!! json_encode(old('cover_id', $movie->cover_id)) !!},
                    backdrop_id: {!! json_encode(old('backdrop_id', $movie->backdrop_id)) !!},
                    created_at: {!! json_encode(old('created_at', $movie->created_at ? $movie->created_at->format('Y-m-d\TH:i') : null)) !!}
                },
                coverPreview: null,
                backdropPreview: null,
                initialCoverUrl: {!! json_encode($movie->cover_url) !!},
                initialBackdropUrl: {!! json_encode($movie->backdrop_url) !!},

                getImageUrl(id, type) {
                    if (!id) return null;
                    if (id.startsWith('http')) return id;
                    if (id.startsWith('/')) {
                        const base = type === 'cover' ? 'https://image.tmdb.org/t/p/w500' : 'https://image.tmdb.org/t/p/w1280';
                        return base + id;
                    }
                    if (id.includes('/')) return `/storage/${id}`;
                    return type === 'cover' ? this.initialCoverUrl : this.initialBackdropUrl;
                },

                handleFileChange(event, type) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            if (type === 'cover') {
                                this.coverPreview = e.target.result;
                            } else {
                                this.backdropPreview = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                },

                initQuill() {
                    const setup = () => {
                        if (this.quill) return;
                        
                        this.quill = new Quill('#overview-editor', {
                            theme: 'snow',
                            modules: {
                                toolbar: {
                                    container: [
                                        ['bold', 'italic', 'underline'],
                                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                        ['actor'],
                                        ['clean']
                                    ],
                                    handlers: {
                                        'actor': function() {
                                            const name = prompt('Name des Schauspielers eingeben:');
                                            if (name) {
                                                const range = this.quill.getSelection();
                                                    this.quill.insertText(range.index, `{!Actor}${name}}`);
                                            }
                                        }
                                    }
                                }
                            },
                            placeholder: 'Filmhandlung hier eingeben...'
                        });

                        if (this.formData.overview) {
                            this.quill.root.innerHTML = this.formData.overview;
                        }

                        this.quill.on('text-change', () => {
                            this.formData.overview = this.quill.root.innerHTML;
                        });

                        this.$watch('formData.overview', value => {
                            if (this.quill && value !== this.quill.root.innerHTML) {
                                this.quill.root.innerHTML = value || '';
                            }
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
                },

                openModal() {
                    this.showModal = true;
                    if (this.results.length === 0) {
                        this.search();
                    }
                },

                search() {
                    if (this.searchQuery.length < 3) return;
                    this.loading = true;
                    const type = this.formData.collection_type === 'Serie' ? 'tv' : 'movie';
                    
                    fetch(`{{ route('admin.tmdb.search') }}?query=${encodeURIComponent(this.searchQuery)}&type=${type}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data.results || [];
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                },

                selectItem(item) {
                    const type = this.formData.collection_type === 'Serie' ? 'tv' : 'movie';
                    this.loading = true;

                    fetch(`{{ route('admin.tmdb.details') }}?tmdb_id=${item.id}&type=${type}`)
                        .then(res => res.json())
                        .then(data => {
                            this.formData.title = data.title || data.name;
                            this.formData.year = (data.release_date || data.first_air_date || '').substring(0, 4);
                            this.formData.genre = (data.genres || []).map(g => g.name).join(', ');
                            this.formData.runtime = data.runtime || (data.episode_run_time ? data.episode_run_time[0] : null);
                            this.formData.rating = (data.vote_average !== undefined && data.vote_average !== null) ? Math.round(data.vote_average * 10) / 10 : null;
                            this.formData.overview = data.overview;
                            this.formData.tmdb_id = data.id;

                            if (data.poster_path) {
                                this.formData.cover_id = data.poster_path;
                            }
                            if (data.backdrop_path) {
                                this.formData.backdrop_id = data.backdrop_path;
                            }

                            if (data.videos && data.videos.results) {
                                const trailer = data.videos.results.find(v => v.site === 'YouTube' && (v.type === 'Trailer' || v.type === 'Teaser'));
                                if (trailer) {
                                    this.formData.trailer_url = `https://www.youtube.com/watch?v=${trailer.key}`;
                                }
                            }

                            let fskRating = null;
                            if (data.release_dates && data.release_dates.results) {
                                const de = data.release_dates.results.find(r => r.iso_3166_1 === 'DE');
                                if (de && de.release_dates) {
                                    const cert = de.release_dates.find(rd => rd.certification);
                                    if (cert) fskRating = cert.certification;
                                }
                            } else if (data.content_ratings && data.content_ratings.results) {
                                const de = data.content_ratings.results.find(r => r.iso_3166_1 === 'DE');
                                if (de) fskRating = de.rating;
                            }

                            if (fskRating) {
                                const digits = fskRating.toString().replace(/[^0-9]/g, '');
                                if (digits) this.formData.rating_age = digits;
                            }

                            this.showModal = false;
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                }
            };
        }

        function actorManagement() {
            return {
                searchQuery: '',
                searchResults: [],
                loading: false,
                actors: {!! json_encode($movie->actors()->orderBy('pivot_sort_order')->get()->map(function($actor) {
                    return [
                        'id' => $actor->id,
                        'first_name' => $actor->first_name,
                        'last_name' => $actor->last_name,
                        'profile_path' => $actor->profile_path,
                        'pivot' => [
                            'role' => $actor->pivot->role,
                            'is_main_role' => (bool)$actor->pivot->is_main_role,
                            'sort_order' => $actor->pivot->sort_order
                        ]
                    ];
                })) !!},

                searchActors() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    this.loading = true;
                    fetch(`{{ route('admin.actors.search') }}?q=${encodeURIComponent(this.searchQuery)}`)
                        .then(res => res.json())
                        .then(data => {
                            // Filter out already added actors
                            const existingIds = this.actors.map(a => a.id);
                            this.searchResults = data.filter(a => !existingIds.includes(a.id));
                            this.loading = false;
                        });
                },

                addActor(actor) {
                    this.actors.push({
                        ...actor,
                        pivot: {
                            role: '',
                            is_main_role: false,
                            sort_order: this.actors.length
                        }
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeActor(index) {
                    this.actors.splice(index, 1);
                }
            };
        }

        if (window.Alpine) {
            Alpine.data('tmdbSearch', tmdbSearch);
            Alpine.data('actorManagement', actorManagement);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('tmdbSearch', tmdbSearch);
                Alpine.data('actorManagement', actorManagement);
            });
        }
    </script>
    @endpush
</x-admin-layout>