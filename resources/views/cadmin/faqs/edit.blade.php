@extends('cadmin.layout')

@section('header_title', 'FAQ bearbeiten')

@section('content')
<div class="max-w-4xl animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Breadcrumb-like Back Button -->
    <a href="{{ route('cadmin.faqs.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-rose-500 transition-colors mb-8 group">
        <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
        Zurück zur Übersicht
    </a>

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl" x-data="faqForm()">
        <div class="px-10 py-8 border-b border-white/5 bg-white/[0.02]">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">FAQ Dokumentation</h3>
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em] mt-1">Bearbeite die Details der bestehenden Frage</p>
        </div>

        <form action="{{ route('cadmin.faqs.update', $faq) }}" method="POST" class="p-10 space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Question Field -->
            <div class="space-y-2">
                <label for="question" class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Frage</label>
                <input type="text" name="question" id="question" value="{{ old('question', $faq->question) }}" required
                       class="block w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none font-medium">
                @error('question') <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-2 ms-1">{{ $message }}</p> @enderror
            </div>

            <!-- Answer Field (Quill) -->
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Antwort</label>
                <div class="rounded-2xl overflow-hidden border border-white/10">
                    <div id="answer-editor" class="quill-editor" style="min-height: 250px; background: rgba(5,5,5,0.4); color: #fff;"></div>
                </div>
                <input type="hidden" name="answer" id="answer" x-model="formData.answer">
                @error('answer') <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-2 ms-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                <!-- Sort Order -->
                <div class="space-y-2">
                    <label for="sort_order" class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Sortierung (0-999)</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $faq->sort_order) }}"
                           class="block w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none font-bold">
                </div>

                <!-- Status Switch -->
                <div class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Status</label>
                    <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $faq->is_active) ? 'checked' : '' }} 
                               class="opacity-0 absolute inset-0 w-full h-full cursor-pointer z-10 peer">
                        <div class="w-12 h-6 bg-white/10 rounded-full p-1 transition-all peer-checked:bg-emerald-500">
                            <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
                        </div>
                        <span class="ms-4 text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Öffentlich sichtbar</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end pt-8 border-t border-white/5">
                <button type="submit" class="px-10 py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-rose-900/40 transition-all transform active:scale-95 flex items-center gap-3">
                    <i class="bi bi-arrow-repeat"></i>
                    FAQ Aktualisieren
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border: none !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; background: rgba(255,255,255,0.02) !important; padding: 15px !important; }
    .ql-container.ql-snow { border: none !important; font-family: 'Inter', sans-serif !important; font-size: 0.95rem !important; }
    .ql-editor { color: #fff !important; min-height: 250px; line-height: 1.6 !important; }
    .ql-snow .ql-stroke { stroke: #aaa !important; }
    .ql-snow .ql-fill { fill: #aaa !important; }
    .ql-snow .ql-picker { color: #aaa !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    function faqForm() {
        return {
            initialized: false,
            formData: {
                answer: {!! json_encode(old('answer', $faq->answer)) !!}
            },
            init() {
                if (this.initialized) return;
                this.initialized = true;

                const setup = () => {
                    const editorContainer = document.querySelector('#answer-editor');
                    if (!editorContainer || editorContainer.classList.contains('ql-container')) return;

                    const quill = new Quill('#answer-editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'link'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['clean']
                            ]
                        },
                        placeholder: 'Antwort hier verfassen...'
                    });

                    if (this.formData.answer) {
                        quill.root.innerHTML = this.formData.answer;
                    }

                    quill.on('text-change', () => {
                        this.formData.answer = quill.root.innerHTML;
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

