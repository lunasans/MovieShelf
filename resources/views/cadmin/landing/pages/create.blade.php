@extends('cadmin.layout')

@section('header_title', 'Neue Unterseite')

@section('content')
<div class="max-w-4xl animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <a href="{{ route('cadmin.landing.pages.index') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-gray-500 hover:text-rose-500 transition-colors mb-8 group">
        <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
        Zurück zur Übersicht
    </a>

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl" x-data="pageForm()">
        <div class="px-10 py-8 border-b border-white/5 bg-white/[0.02]">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Neue Unterseite</h3>
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em] mt-1">Öffentlich erreichbar unter /p/{slug}</p>
        </div>

        <form action="{{ route('cadmin.landing.pages.store') }}" method="POST" class="p-10 space-y-8">
            @csrf
            @include('cadmin.landing.pages._form')
            <div class="flex justify-end pt-8 border-t border-white/5">
                <button type="submit" class="px-10 py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-rose-900/40 transition-all active:scale-95 flex items-center gap-3">
                    <i class="bi bi-save2-fill"></i>
                    Seite speichern
                </button>
            </div>
        </form>
    </div>
</div>
@include('cadmin.landing.pages._quill_scripts')
@endsection
