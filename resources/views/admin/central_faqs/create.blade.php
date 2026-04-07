@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Neue FAQ erstellen</h1>
    <a href="{{ route('admin.faqs.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
        <i class="bi bi-arrow-left"></i> Zurück zur Liste
    </a>
</div>

<div class="card glass" x-data="faqForm()">
    <form action="{{ route('admin.faqs.store') }}" method="POST">
        @csrf
        <div style="margin-bottom: 2rem;">
            <label for="question" style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Frage</label>
            <input type="text" name="question" id="question" value="{{ old('question') }}" required
                   style="width: 100%; background: #000; border: 1px solid #333; border-radius: 0.5rem; padding: 0.75rem 1rem; color: #fff; font-family: inherit; font-size: 1rem; box-sizing: border-box;">
            @error('question') <div style="color: var(--primary); font-size: 0.75rem; margin-top: 0.5rem;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom: 2rem;">
            <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Antwort</label>
            <div id="answer-editor" class="quill-editor" style="min-height: 200px; background: #000; border: 1px solid #333; border-radius: 0.5rem; color: #fff;"></div>
            <input type="hidden" name="answer" id="answer" x-model="formData.answer">
            @error('answer') <div style="color: var(--primary); font-size: 0.75rem; margin-top: 0.5rem;">{{ $message }}</div> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
            <div>
                <label for="sort_order" style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Sortier-Reihenfolge (0-999)</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                       style="width: 100%; background: #000; border: 1px solid #333; border-radius: 0.5rem; padding: 0.75rem 1rem; color: #fff; font-family: inherit; font-size: 1rem; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                <div style="padding-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 1.2rem; height: 1.2rem; cursor: pointer; accent-color: var(--primary);">
                    <span style="font-size: 0.9rem;">Sichtbar auf Landingpage</span>
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn">FAQ speichern</button>
        </div>
    </form>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border: 1px solid #333 !important; background: #1a1a1a !important; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; }
    .ql-container.ql-snow { border: 1px solid #333 !important; border-top: none !important; border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; font-family: inherit !important; }
    .ql-editor { color: #fff !important; min-height: 200px; }
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
                answer: {!! json_encode(old('answer')) !!}
            },
            init() {
                if (this.initialized) return;
                this.initialized = true;

                if (document.querySelector('#answer-editor').classList.contains('ql-container')) return;

                const quill = new Quill('#answer-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'link'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['clean']
                        ]
                    },
                    placeholder: 'Antwort eingeben...'
                });

                if (this.formData.answer) {
                    quill.root.innerHTML = this.formData.answer;
                }

                quill.on('text-change', () => {
                    this.formData.answer = quill.root.innerHTML;
                });
            }
        }
    }
</script>
@endpush
@endsection
