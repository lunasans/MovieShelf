@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border: none !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; background: rgba(255,255,255,0.02) !important; padding: 15px !important; }
    .ql-container.ql-snow { border: none !important; font-family: 'Inter', sans-serif !important; font-size: 0.95rem !important; }
    .ql-editor { color: #fff !important; min-height: 300px; line-height: 1.7 !important; }
    .ql-snow .ql-stroke { stroke: #aaa !important; }
    .ql-snow .ql-fill { fill: #aaa !important; }
    .ql-snow .ql-picker { color: #aaa !important; }
    .ql-snow .ql-picker-options { background: #0f172a !important; border-color: rgba(255,255,255,0.1) !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
function pageForm() {
    return {
        slug: document.getElementById('slug')?.value ?? '',
        formData: {
            content: {!! json_encode($existingContent ?? old('content'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
        },
        updateSlug() {
            if (!this.slug) {
                this.slug = this.$el.querySelector('#title').value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
            }
        },
        init() {
            const setup = () => {
                const el = document.querySelector('#content-editor');
                if (!el || el.classList.contains('ql-container')) return;

                const quill = new Quill('#content-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, 4, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['link', 'blockquote', 'code-block'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                            ['clean']
                        ]
                    },
                    placeholder: 'Seiteninhalt hier verfassen…'
                });

                if (this.formData.content) {
                    quill.root.innerHTML = this.formData.content;
                }

                quill.on('text-change', () => {
                    this.formData.content = quill.root.innerHTML;
                });
            };

            typeof Quill === 'undefined'
                ? setInterval(() => { if (typeof Quill !== 'undefined') { clearInterval(arguments.callee); setup(); } }, 50)
                : this.$nextTick(setup);
        }
    }
}
</script>
@endpush
