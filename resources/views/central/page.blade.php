@extends('layouts.central')

@section('title', $page->title . ' — ' . \App\Models\Setting::get('saas_name', config('app.name')))

@section('content')
<main class="relative z-10 pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        
        {{-- Breadcrumb / Back --}}
        <div class="mb-12">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-[#CC4B06] transition-colors group">
                <i class="bi bi-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                Zurück zur Startseite
            </a>
        </div>

        <article class="animate-reveal active">
            <h1 class="display mb-12" style="font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.1;">
                {{ $page->title }}
            </h1>

            <div class="glass-ultra rounded-[2rem] p-8 md:p-12 shadow-sm border border-gray-100">
                <div class="prose prose-gray max-w-none 
                            prose-headings:font-bold prose-headings:text-black
                            prose-p:text-gray-600 prose-p:leading-relaxed
                            prose-a:text-[#CC4B06] prose-a:font-semibold hover:prose-a:underline
                            prose-strong:text-black">
                    {!! \Purifier::clean($page->content, 'richtext') !!}
                </div>
            </div>
        </article>

    </div>
</main>

<style>
    /* Ensure the content fits the platinum aesthetic */
    body {
        background-color: #FFFFFF !important;
    }
    .prose h2 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }
</style>
@endsection
