@extends('cadmin.layout')

@section('title', 'Template Bearbeiten - Global ACP')
@section('header_title', 'Template Bearbeiten')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('cadmin.email-templates.index') }}" class="text-gray-400 hover:text-white flex items-center gap-2 text-sm transition-colors group">
            <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
            Zurück zur Übersicht
        </a>
    </div>

    <div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="p-8 border-b border-white/5 bg-white/[0.02]">
            <h3 class="text-xl font-black text-white">Editor: {{ $template->name }}</h3>
            <p class="text-gray-400 text-sm mt-1">Passe den Betreff und den Inhalt der E-Mail an.</p>
        </div>

        <form action="{{ route('cadmin.email-templates.update', $template) }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 gap-8">
                <!-- Subject -->
                <div class="space-y-3">
                    <label for="subject" class="text-xs font-black uppercase tracking-widest text-rose-500/80">E-Mail Betreff</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" 
                           class="w-full bg-white/[0.03] border border-white/5 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-1 focus:ring-rose-500/50 transition-all">
                </div>

                <!-- Content -->
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <label for="content" class="text-xs font-black uppercase tracking-widest text-rose-500/80">E-Mail Inhalt (Markdown / Blade)</label>
                        @if($template->variables_hint)
                            <div class="group relative">
                                <span class="text-[10px] bg-rose-500/10 text-rose-400 px-3 py-1 rounded-full border border-rose-500/20 cursor-help">
                                    <i class="bi bi-info-circle mr-1"></i> Verfügbare Variablen
                                </span>
                                <div class="absolute right-0 bottom-full mb-3 w-64 p-4 glass rounded-2xl shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-300 z-50 transform translate-y-2 group-hover:translate-y-0">
                                    <p class="text-[10px] leading-relaxed text-gray-300 font-mono italic">
                                        {{ $template->variables_hint }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <textarea id="content" name="content" rows="15" 
                              class="w-full bg-black/40 border border-white/5 rounded-2xl px-6 py-6 text-gray-200 font-mono text-sm leading-relaxed focus:outline-none focus:border-rose-500/50 transition-all custom-scrollbar">{{ old('content', $template->content) }}</textarea>
                    <p class="text-[10px] text-gray-500 italic">Hinweis: Du kannst Laravel Blade-Syntax (z.B. @{{ $user->name }}) verwenden.</p>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-4">
                <a href="{{ route('cadmin.email-templates.index') }}" 
                   class="px-8 py-4 rounded-2xl bg-white/5 text-white font-bold hover:bg-white/10 transition-all border border-white/5">
                    Abbrechen
                </a>
                <button type="submit" 
                        class="px-8 py-4 rounded-2xl bg-gradient-to-br from-rose-600 to-red-700 text-white font-black shadow-lg shadow-rose-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    Speichern & Aktualisieren
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
