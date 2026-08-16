<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – Abgemeldet</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0c0c0e;
            color: #fff;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .card {
            max-width: 420px;
            margin: 24px;
            padding: 48px 40px;
            text-align: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            font-size: 28px;
        }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { color: rgba(255, 255, 255, 0.5); font-size: 14px; line-height: 1.6; margin: 0 0 24px; }
        a {
            display: inline-block;
            padding: 12px 28px;
            background: #2563eb;
            color: #fff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#10003;</div>
        <h1>Abgemeldet</h1>
        <p>
            Hallo {{ $user->name }}, du erhältst keine E-Mails mehr zu neuen Episoden.
            Du kannst die Benachrichtigungen jederzeit in deinem Profil unter
            App-Einstellungen wieder aktivieren.
        </p>
        <a href="{{ route('dashboard') }}">Zur Startseite</a>
    </div>
</body>
</html>
