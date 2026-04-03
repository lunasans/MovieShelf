<x-admin-layout>
    @section('header_title', 'Neuer Schauspieler')

    <div class="max-w-4xl mx-auto" x-data="{ 
        bio: @js(old('bio', '')),
        quill: null,
        init() {
            const setup = () => {
                this.quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['clean']
                        ]
                    },
                    placeholder: 'Biografie hier eingeben...'
                });
                if (this.bio) this.quill.root.innerHTML = this.bio;
                this.quill.on('text-change', () => {
                    this.bio = this.quill.root.innerHTML;
                });
            };
            if (typeof Quill === 'undefined') {
                const interval = setInterval(() => {
                    if (typeof Quill !== 'undefined') { clearInterval(interval); setup(); }
                }, 50);
            } else { this.$nextTick(() => setup()); }
        }
    }">
        <div class="mb-6">
            <a href="{{ route('admin.actors.index') }}" class="text-sm text-gray-500 hover:text-blue-400 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Zurück zur Übersicht
            </a>
        </div>

        @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <style>
            .ql-toolbar.ql-snow {
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                background: rgba(15, 23, 42, 0.8) !important;
                backdrop-filter: blur(10px);
                border-top-left-radius: 1.5rem;
                border-top-right-radius: 1.5rem;
                padding: 12px 20px !important;
            }
            .ql-container.ql-snow {
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-top: none !important;
                background: rgba(15, 23, 42, 0.5) !important;
                border-bottom-left-radius: 1.5rem;
                border-bottom-right-radius: 1.5rem;
                font-family: inherit !important;
                font-size: 0.875rem !important;
            }
            .ql-editor {
                min-height: 250px;
                color: rgba(255, 255, 255, 0.9) !important;
                padding: 20px !important;
                line-height: 1.625 !important;
            }
            .ql-snow .ql-stroke { stroke: rgba(255, 255, 255, 0.6) !important; }
            .ql-snow .ql-fill { fill: rgba(255, 255, 255, 0.6) !important; }
            .ql-snow .ql-picker { color: rgba(255, 255, 255, 0.6) !important; }
            .ql-snow.ql-toolbar button:hover .ql-stroke,
            .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #3b82f6 !important; }
            .ql-snow.ql-toolbar button:hover .ql-fill,
            .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #3b82f6 !important; }
        </style>
        @endpush

        <form action="{{ route('admin.actors.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- General Info -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-person-plus text-blue-400"></i>
                    Persönliche Daten
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Vorname *</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        @error('first_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nachname *</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        @error('last_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birth_date" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Geburtsdatum</label>
                        <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="nationality" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nationalität</label>
                        <input type="text" name="nationality" id="nationality" value="{{ old('nationality') }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label for="birth_place" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Geburtsort</label>
                        <input type="text" name="birth_place" id="birth_place" value="{{ old('birth_place') }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>
                </div>
            </div>

            <!-- Biography -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-file-text text-purple-400"></i>
                    Biografie
                </h3>

                <div>
                    <div id="quill-editor"></div>
                    <input type="hidden" name="bio" x-model="bio">
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('admin.actors.index') }}" class="px-8 py-3 rounded-2xl font-bold text-sm text-gray-400 hover:bg-white/5 transition-all">
                    Abbrechen
                </a>
                <button type="submit" class="px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-person-check"></i>
                    Schauspieler anlegen
                </button>
            </div>
        </form>
    </div>
    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    @endpush
</x-admin-layout>
