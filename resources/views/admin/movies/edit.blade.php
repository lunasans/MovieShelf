<x-admin-layout>
    @section('header_title', 'Film bearbeiten')

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.movies.index') }}" class="text-sm text-gray-500 hover:text-blue-400 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Zurück zur Übersicht
            </a>
        </div>

        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- General Info -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-info-circle text-blue-400"></i>
                    Allgemeine Informationen
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Titel *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $movie->title) }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        @error('title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="year" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Erscheinungsjahr *</label>
                        <input type="number" name="year" id="year" value="{{ old('year', $movie->year) }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="collection_type" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Medientyp *</label>
                        <select name="collection_type" id="collection_type" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                            <option value="Owned" {{ old('collection_type', $movie->collection_type) == 'Owned' ? 'selected' : '' }}>Owned</option>
                            <option value="Serie" {{ old('collection_type', $movie->collection_type) == 'Serie' ? 'selected' : '' }}>Serie</option>
                            <option value="Stream" {{ old('collection_type', $movie->collection_type) == 'Stream' ? 'selected' : '' }}>Stream</option>
                        </select>
                    </div>

                    <div>
                        <label for="genre" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Genre</label>
                        <input type="text" name="genre" id="genre" value="{{ old('genre', $movie->genre) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="runtime" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Laufzeit (Min.)</label>
                        <input type="number" name="runtime" id="runtime" value="{{ old('runtime', $movie->runtime) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="rating_age" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">FSK</label>
                        <select name="rating_age" id="rating_age"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                            <option value="">Keine Angabe</option>
                            @foreach([0, 6, 12, 16, 18] as $age)
                                <option value="{{ $age }}" {{ old('rating_age', $movie->rating_age) == $age ? 'selected' : '' }}>FSK {{ $age }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="created_at" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Hinzugefügt am</label>
                        <input type="date" name="created_at" id="created_at" value="{{ old('created_at', $movie->created_at?->format('Y-m-d')) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-play-circle text-red-500"></i>
                    Medien & Links
                </h3>

                <div class="space-y-6">
                    <div>
                        <label for="trailer_url" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Trailer URL (YouTube)</label>
                        <input type="url" name="trailer_url" id="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}"
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="overview" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Handlung / Beschreibung</label>
                        <textarea name="overview" id="overview" rows="5"
                                  class="w-full bg-white/5 border border-white/10 rounded-3xl py-4 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all leading-relaxed">{{ old('overview', $movie->overview) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('admin.movies.index') }}" class="px-8 py-3 rounded-2xl font-bold text-sm text-gray-400 hover:bg-white/5 transition-all">
                    Abbrechen
                </a>
                <button type="submit" class="px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-save"></i>
                    Änderungen speichern
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
