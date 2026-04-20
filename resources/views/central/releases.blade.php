@extends('layouts.central')

@section('content')

<section class="section" style="padding-top: 5rem; min-height: 80vh;">
    <div class="container" style="max-width: 820px;">

        <div style="text-align:center; margin-bottom: 4rem;">
            <span class="eyebrow">Versionsarchiv</span>
            <h1 class="display" style="font-size:clamp(2rem,5vw,3.2rem); margin:.5rem 0 0">
                MovieShelf Desktop<br>
                <span class="display-italic">Changelog.</span>
            </h1>
            <div class="divider centered"></div>
            <p style="color:var(--muted); margin-top: 1rem;">
                Alle öffentlichen Versionen mit Änderungsprotokoll und Download-Links.
            </p>
        </div>

        @forelse($releases as $release)
        <div style="
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
            background: var(--surface);
            transition: border-color 0.2s;
        " onmouseover="this.style.borderColor='rgba(204,75,6,0.3)'" onmouseout="this.style.borderColor='var(--border)'">

            {{-- Version Header --}}
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
                <div>
                    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                        <span style="font-size:1.35rem; font-weight:900; color:#fff; font-family:var(--font-display, inherit);">
                            {{ $release->version }}
                        </span>
                        @if($loop->first)
                        <span style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:var(--accent); background:rgba(204,75,6,.12); border:1px solid rgba(204,75,6,.25); border-radius:999px; padding:3px 10px;">
                            Neueste Version
                        </span>
                        @endif
                    </div>
                    <span style="font-size:11px; color:var(--muted); font-weight:600; margin-top:4px; display:block;">
                        {{ $release->created_at->translatedFormat('d. F Y') }}
                    </span>
                </div>

                {{-- Platform Downloads --}}
                <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
                    @if($release->download_url)
                    <a href="{{ $release->download_url }}"
                       style="display:inline-flex; align-items:center; gap:6px; padding:.45rem 1rem; border-radius:.75rem; font-size:11px; font-weight:700; background:var(--accent); color:#fff; text-decoration:none; white-space:nowrap;"
                       title="{{ $release->file_hash ? 'SHA-256: '.$release->file_hash : '' }}">
                        <i class="bi bi-windows"></i> Windows
                        @if($release->file_hash)
                        <i class="bi bi-shield-check" style="color:rgba(255,255,255,.6); font-size:10px;" title="SHA-256 verifiziert"></i>
                        @endif
                    </a>
                    @endif

                    @if($release->download_url_linux_appimage)
                    <a href="{{ $release->download_url_linux_appimage }}"
                       style="display:inline-flex; align-items:center; gap:6px; padding:.45rem 1rem; border-radius:.75rem; font-size:11px; font-weight:700; border:1px solid var(--border); color:var(--text); text-decoration:none; white-space:nowrap;"
                       onmouseover="this.style.borderColor='rgba(204,75,6,.4)'" onmouseout="this.style.borderColor='var(--border)'"
                       title="{{ $release->file_hash_linux_appimage ? 'SHA-256: '.$release->file_hash_linux_appimage : '' }}">
                        <i class="bi bi-ubuntu"></i> AppImage
                        @if($release->file_hash_linux_appimage)
                        <i class="bi bi-shield-check" style="color:#10B981; font-size:10px;" title="SHA-256 verifiziert"></i>
                        @endif
                    </a>
                    @endif

                    @if($release->download_url_linux_deb)
                    <a href="{{ $release->download_url_linux_deb }}"
                       style="display:inline-flex; align-items:center; gap:6px; padding:.45rem 1rem; border-radius:.75rem; font-size:11px; font-weight:700; border:1px solid var(--border); color:var(--text); text-decoration:none; white-space:nowrap;"
                       onmouseover="this.style.borderColor='rgba(204,75,6,.4)'" onmouseout="this.style.borderColor='var(--border)'"
                       title="{{ $release->file_hash_linux_deb ? 'SHA-256: '.$release->file_hash_linux_deb : '' }}">
                        <i class="bi bi-terminal"></i> Debian
                        @if($release->file_hash_linux_deb)
                        <i class="bi bi-shield-check" style="color:#10B981; font-size:10px;" title="SHA-256 verifiziert"></i>
                        @endif
                    </a>
                    @endif
                </div>
            </div>

            {{-- Changelog --}}
            @if($release->changelog)
            <div style="border-top:1px solid var(--border); padding-top:1.25rem;">
                <div style="font-size:.85rem; color:var(--muted); line-height:1.75; white-space:pre-line;">{{ $release->changelog }}</div>
            </div>
            @endif

        </div>
        @empty
        <div style="text-align:center; padding:5rem 0; color:var(--muted);">
            <i class="bi bi-archive" style="font-size:2.5rem; display:block; margin-bottom:1rem; opacity:.3;"></i>
            Noch keine öffentlichen Releases vorhanden.
        </div>
        @endforelse

        <div style="text-align:center; margin-top:3rem;">
            <a href="{{ route('landing') }}" style="color:var(--muted); font-size:.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem;">
                <i class="bi bi-arrow-left"></i> Zurück zur Startseite
            </a>
        </div>

    </div>
</section>

@endsection
