<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Noch nicht aktiviert – MovieShelf</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/logo/logo_small.png') }}">
    <link rel="stylesheet" href="{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans.css') }}">
    {{-- Eigenständige Seite ohne Build-Assets, Design angelehnt an layouts/central --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background-color: #0c0c0e;
            color: #F9FAFB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: #0c0c0e; overflow: hidden; }
        .mesh-circle { position: absolute; border-radius: 50%; filter: blur(150px); opacity: 0.25; }
        .mesh-rose { background: #e11d48; width: 900px; height: 900px; top: -40%; left: -15%; }
        .mesh-indigo { background: #4f46e5; width: 800px; height: 800px; bottom: -30%; right: -10%; }

        .card {
            width: 100%;
            max-width: 720px;
            background: rgba(18, 18, 22, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            text-align: center;
            animation: reveal 0.9s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes reveal {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo { height: 44px; object-fit: contain; margin-bottom: 2rem; }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.9rem;
        }

        p.lead {
            font-size: 0.95rem;
            line-height: 1.65;
            color: #9CA3AF;
            margin-bottom: 2.25rem;
        }
        p.lead strong { color: #F9FAFB; }

        .btn {
            display: inline-block;
            background: #e11d48;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.95rem 2.25rem;
            border-radius: 8px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn:hover { background: #be123c; }
        .btn:active { transform: scale(0.97); }

        .hint {
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.75rem;
            color: #6B7280;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .card { padding: 2.25rem 1.5rem; border-radius: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg">
        <div class="mesh-circle mesh-rose"></div>
        <div class="mesh-circle mesh-indigo"></div>
    </div>

    <main class="card">
        <img src="{{ asset('img/logo/logo.png') }}" alt="MovieShelf" class="logo">

        <h1>Dieses MovieShelf ist noch nicht aktiviert</h1>

        <p class="lead">
            <strong>{{ $tenantId }}.{{ $centralDomain }}</strong> wurde reserviert, aber noch nicht freigeschaltet.
            Bitte prüfe dein E-Mail-Postfach und klicke auf den Aktivierungslink –
            danach ist dein Filmregal sofort einsatzbereit.
        </p>

        <a href="{{ $centralUrl }}" class="btn">Zur MovieShelf Cloud</a>

        <p class="hint">
            Keine E-Mail erhalten? Schau auch im Spam-Ordner nach.
            Je nach Einstellung wird dein Regal alternativ von uns manuell freigeschaltet –
            in dem Fall bekommst du eine E-Mail, sobald es so weit ist.
        </p>
    </main>

</body>
</html>
