<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="background-color:#020617;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} — {{ \App\Models\Setting::get('saas_name', config('app.name')) }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet"> <!-- NOSONAR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-white min-h-screen bg-[#020617]">

    <!-- Background -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-[#020617] via-[#0a0f1e] to-[#020617]"></div>
        <div class="absolute top-[10%] -left-[10%] w-[50%] h-[50%] bg-rose-600/5 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-red-900/5 rounded-full blur-[100px]"></div>
    </div>

    <!-- Header -->
    <header class="relative z-10 border-b border-white/5 bg-black/20 backdrop-blur-xl">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <x-application-logo class="h-8 w-auto group-hover:scale-105 transition-transform" />
                <span class="text-sm font-black text-white uppercase tracking-widest">
                    {{ \App\Models\Setting::get('saas_name', config('app.name')) }}
                </span>
            </a>
            <a href="{{ route('landing') }}"
               class="text-[10px] font-black text-gray-500 hover:text-white uppercase tracking-widest transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Startseite
            </a>
        </div>
    </header>

    <!-- Content -->
    <main class="relative z-10 max-w-4xl mx-auto px-6 py-16 md:py-24">
        <article>
            <h1 class="text-3xl md:text-5xl font-black text-white uppercase tracking-tight mb-12">
                {{ $page->title }}
            </h1>
            <div class="prose prose-invert prose-lg max-w-none
                        prose-headings:font-black prose-headings:uppercase prose-headings:tracking-tight
                        prose-a:text-rose-400 prose-a:no-underline hover:prose-a:text-rose-300
                        prose-strong:text-white prose-code:text-rose-300
                        prose-hr:border-white/10">
                {!! \Purifier::clean($page->content, 'richtext') !!}
            </div>
        </article>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/5 py-10 mt-16">
        <div class="max-w-4xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-[10px] text-gray-600 font-bold uppercase tracking-widest">
                &copy; {{ date('Y') }} {{ \App\Models\Setting::get('saas_name', config('app.name')) }}
            </p>
            @if($navPages->isNotEmpty())
            <nav class="flex items-center gap-6">
                @foreach($navPages as $navPage)
                    <a href="{{ route('landing.page', $navPage->slug) }}"
                       class="text-[10px] font-bold text-gray-500 hover:text-white uppercase tracking-widest transition-colors
                              {{ $navPage->slug === $page->slug ? 'text-white' : '' }}">
                        {{ $navPage->title }}
                    </a>
                @endforeach
            </nav>
            @endif
        </div>
    </footer>

</body>
</html>
