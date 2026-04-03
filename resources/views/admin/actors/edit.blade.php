<x-admin-layout>
    @section('header_title', 'Star bearbeiten')

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
    </style>
    @endpush

    <div class="max-w-4xl mx-auto" x-data="actorForm()">
        <div class="mb-10 flex items-center justify-between">
            <a href="{{ route('admin.actors.index') }}" class="text-xs font-black text-white/30 hover:text-rose-400 uppercase tracking-[0.2em] transition-all flex items-center gap-2 group">
                <i class="bi bi-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
                Zurück zur Liste
            </a>
        </div>

        <form action="{{ route('admin.actors.update', $actor) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Profile Image Selection -->
                <div class="md:col-span-1 space-y-6">
                    <div class="glass p-8 rounded-[3rem] border-white/5 shadow-2xl">
                        <label class="block text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-6 text-center italic">Profilbild</label>
                        
                        <div class="relative group aspect-square rounded-[2rem] overflow-hidden bg-white/5 border border-white/10 flex flex-col items-center justify-center cursor-pointer hover:border-rose-500/40 transition-all shadow-xl"
                             x-data="{ preview: null }"
                             @click="$refs.profileInput.click()">
                            
                            @if($actor->profile_path)
                                <img src="{{ $actor->profile_url }}" 
                                     class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-110"
                                     :class="preview ? 'opacity-40' : 'opacity-100'">
                            @endif

                            <template x-if="preview">
                                <img :src="preview" class="absolute inset-0 w-full h-full object-cover z-20">
                            </template>

                            <div class="relative z-10 flex flex-col items-center gap-4 p-6 text-center">
                                <div class="w-14 h-14 rounded-full bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shadow-lg shadow-rose-500/10">
                                    <i class="bi bi-camera-fill text-xl"></i>
                                </div>
                                <span class="text-[9px] font-black text-white/30 uppercase tracking-widest leading-tight">Bild auswählen</span>
                            </div>

                            <input type="file" name="profile_upload" x-ref="profileInput" class="hidden" accept="image/*" 
                                   @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { preview = e.target.result }; reader.readAsDataURL(file); }">
                        </div>
                        @error('profile_upload') <p class="text-rose-400 text-[10px] mt-4 text-center font-bold uppercase tracking-widest">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Actor Details -->
                <div class="md:col-span-2 space-y-10">
                    <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                        <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-10 flex items-center gap-3">
                            <i class="bi bi-person-badge-fill text-rose-500"></i>
                            Personendaten
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="first_name" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Vorname *</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $actor->first_name) }}" required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white font-bold focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                                @error('first_name') <p class="text-rose-400 text-[10px] mt-2 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Nachname *</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $actor->last_name) }}" required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white font-bold focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                                @error('last_name') <p class="text-rose-400 text-[10px] mt-2 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="nationality" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Nationalität</label>
                                <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $actor->nationality) }}" placeholder="z.B. USA, Deutschland"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                            </div>

                            <div class="md:col-span-2">
                                <label for="tmdb_id" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">TMDb ID</label>
                                <div class="relative">
                                    <input type="number" name="tmdb_id" id="tmdb_id" value="{{ old('tmdb_id', $actor->tmdb_id) }}"
                                           class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-white font-mono focus:outline-none focus:border-rose-500/50 transition-all">
                                    <i class="bi bi-link-45deg absolute right-6 top-1/2 -translate-y-1/2 text-white/10 text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biography Section -->
                    <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                        <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-10 flex items-center gap-3">
                            <i class="bi bi-text-left text-rose-500"></i>
                            Biografie
                        </h3>
                        
                        <div class="rounded-[2rem] overflow-hidden shadow-inner bg-black/20" x-init="initQuill()">
                            <div id="biography-editor"></div>
                            <input type="hidden" name="biography" x-model="formData.biography">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-6 pt-4 mb-20">
                        <a href="{{ route('admin.actors.index') }}" class="px-8 py-4 text-[10px] font-black text-white/20 hover:text-white uppercase tracking-widest transition-all">
                            Verwerfen
                        </a>
                        <button type="submit" class="px-12 py-5 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-rose-600/30 flex items-center gap-4 transform hover:scale-[1.03] active:scale-[0.98]">
                            <i class="bi bi-save2 text-base"></i>
                            Star-Daten sichern
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        function actorForm() {
            return {
                quill: null,
                formData: {
                    biography: {!! json_encode(old('biography', $actor->biography)) !!}
                },
                initQuill() {
                    const setup = () => {
                        if (this.quill) return;
                        
                        this.quill = new Quill('#biography-editor', {
                            theme: 'snow',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    ['clean']
                                ]
                            },
                            placeholder: 'Schreiben Sie hier die Biografie...'
                        });

                        if (this.formData.biography) {
                            this.quill.root.innerHTML = this.formData.biography;
                        }

                        this.quill.on('text-change', () => {
                            this.formData.biography = this.quill.root.innerHTML;
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
            };
        }

        if (window.Alpine) {
            Alpine.data('actorForm', actorForm);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('actorForm', actorForm);
            });
        }
    </script>
    @endpush
</x-admin-layout>
