{{--
    Gemeinsame Huelle fuer die Auth-Seiten eines Regals.

    Aufbau und Farben wie central/auth/layout (fokussierte Glass-Karte statt
    App-Chrome), lediglich der Name des Regals steht statt des MovieShelf-
    Brandings ueber der Ueberschrift.

    Die Farben folgen dem Theme des Regals: app.css haengt die komplette
    rose-Palette an --accent-primary, der Login zieht sie ueber dieselbe
    Variable und passt damit immer zur Oberflaeche dahinter.

    Vorher lief das ueber layouts/app: Damit bekamen ausgeloggte Besucher die
    komplette App-Navigation samt Suchfeld ausgeliefert, und data-theme wurde
    auf Auth-Seiten hart auf "dark" gesetzt – das Theme des Regals war dort
    also ohnehin schon wirkungslos.

    Sections: title, heading, lead, content, hint (optional)
--}}
@php
    $shelfTitle = \App\Models\Setting::get('site_title', config('app.name', 'MovieShelf'));
    $shelfTheme = session('theme', \App\Models\Setting::get('theme', 'default'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $shelfTheme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0c0c0e">
    <title>@yield('title') – {{ $shelfTitle }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/logo/logo_small.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Folgt dem Theme des Regals (app.css); im Standardfall Rose. */
        html {
            --auth-accent: var(--accent-primary);
            --auth-accent-hover: var(--accent-secondary);
            --auth-accent-2: var(--accent-secondary);
        }

        body {
            background: #0c0c0e;
            color: #F9FAFB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        /* Mesh-Gradient wie in layouts/central */
        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: #0c0c0e; overflow: hidden; }
        .mesh-circle { position: absolute; border-radius: 50%; filter: blur(150px); opacity: .22; }
        .mesh-a { background: var(--auth-accent); width: 1200px; height: 1200px; top: -40%; left: -15%; }
        .mesh-b { background: var(--auth-accent-2); width: 1000px; height: 1000px; bottom: -30%; right: -10%; }

        .card {
            width: 100%;
            max-width: 440px;
            background: rgba(18, 18, 22, .7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 24px;
            padding: 2.75rem 2.25rem;
        }

        .brand { display: flex; justify-content: center; margin-bottom: 1.25rem; }
        .brand img { height: 56px; width: 56px; object-fit: contain; }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.9rem;
            line-height: 1.1;
            letter-spacing: -.03em;
            text-transform: uppercase;
            text-align: center;
            margin: 0 0 .6rem;
        }

        .shelf-name {
            display: block;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--auth-accent);
            margin-bottom: .5rem;
            word-break: break-word;
        }

        .lead {
            font-size: .85rem;
            line-height: 1.6;
            color: #9CA3AF;
            text-align: center;
            margin: 0 0 2rem;
        }

        .notice {
            margin-bottom: 1.5rem;
            padding: .85rem 1rem;
            border-radius: 12px;
            font-size: .8rem;
            font-weight: 600;
            line-height: 1.5;
        }
        .notice-error   { border: 1px solid rgba(225, 29, 72, .4); background: rgba(225, 29, 72, .1); color: #fda4af; }
        .notice-success { border: 1px solid rgba(16, 185, 129, .4); background: rgba(16, 185, 129, .1); color: #6ee7b7; }
        .notice p { margin: .15rem 0; }

        .field { margin-bottom: 1.15rem; }

        label {
            display: block;
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9CA3AF;
            margin: 0 0 .4rem .25rem;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 12px;
            padding: .85rem 1rem;
            color: #fff;
            font-family: inherit;
            font-size: .95rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--auth-accent) 20%, transparent);
        }
        input::placeholder { color: #4B5563; }

        .code-input {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: .4em;
            padding: .9rem 1rem;
        }
        .code-input::placeholder { letter-spacing: .2em; font-size: 1rem; font-weight: 400; }

        .btn {
            width: 100%;
            margin-top: .5rem;
            background: var(--auth-accent);
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-weight: 700;
            font-size: .8rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .95rem;
            border-radius: 10px;
            transition: background .2s;
        }
        .btn:hover { background: var(--auth-accent-hover); }

        .btn-ghost {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #9CA3AF;
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, .09); color: #F9FAFB; }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.1rem;
        }

        .remember { display: inline-flex; align-items: center; cursor: pointer; margin: 0; }
        .remember input { accent-color: var(--auth-accent); width: 15px; height: 15px; cursor: pointer; }
        .remember span {
            margin-left: .5rem;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9CA3AF;
        }

        .link-muted {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9CA3AF;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            padding: 0;
        }
        .link-muted:hover { color: #F9FAFB; }

        .hint {
            margin-top: 1.75rem;
            padding-top: 1.35rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
            font-size: .72rem;
            color: #6B7280;
            line-height: 1.65;
            text-align: center;
        }
        .hint a { color: var(--auth-accent); text-decoration: none; }
        .hint a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; border-radius: 20px; }
            h1 { font-size: 1.6rem; }
        }

        @yield('styles')
    </style>
</head>
<body>

    <div class="mesh-bg">
        <div class="mesh-circle mesh-a"></div>
        <div class="mesh-circle mesh-b"></div>
    </div>

    <main class="card">
        <div class="brand">
            <a href="{{ url('/') }}" aria-label="Zur Startseite">
                <img src="{{ asset('img/logo/logo_small.png') }}" alt="">
            </a>
        </div>

        <span class="shelf-name">{{ $shelfTitle }}</span>
        <h1>@yield('heading')</h1>

        {{-- Die 2FA-Seite bringt zwei eigene, umschaltbare Leads mit --}}
        @hasSection('lead')
            <p class="lead">@yield('lead')</p>
        @endif

        @if (session('status'))
            <div class="notice notice-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice notice-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')

        @hasSection('hint')
            <p class="hint">@yield('hint')</p>
        @endif
    </main>

</body>
</html>
