<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shelf-Löschung bestätigen</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #050505; color: #ffffff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #111111; border: 1px solid #333333; border-radius: 12px; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #e11d48; text-transform: uppercase; letter-spacing: 2px; }
        .content { line-height: 1.6; font-size: 16px; color: #cccccc; }
        .warning { background-color: rgba(225, 29, 72, 0.1); border-left: 4px solid #e11d48; padding: 15px; margin: 20px 0; color: #fecdd3; font-weight: bold; }
        .btn-container { text-align: center; margin-top: 40px; }
        .btn { display: inline-block; background-color: #e11d48; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">MovieShelf</div>
        </div>
        <div class="content">
            <h1>Shelf-Löschung bestätigen</h1>
            <p>Hallo,</p>
            <p>wir haben eine Anfrage erhalten, dein MovieShelf <strong>{{ $tenantName }}</strong> unwiderruflich zu löschen.</p>
            
            <div class="warning">
                VORSICHT: Durch diesen Vorgang wird das gesamte Shelf inklusive Datenbank, Filmlisten, Cover-Bildern und deiner Subdomain sofort gelöscht. Dies kann NICHT rückgängig gemacht werden.
            </div>

            <p>Wenn du die Löschung wirklich durchführen möchtest, klicke bitte auf den folgenden Button:</p>
            
            <div class="btn-container">
                <a href="{{ $deletionUrl }}" class="btn">Shelf jetzt löschen</a>
            </div>

            <p style="margin-top: 30px; font-size: 14px;">Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail einfach ignorieren. Das Regal bleibt dann unverändert bestehen.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MovieShelf Cloud. v{{ config('app.shelf_version') }}
        </div>
    </div>
</body>
</html>
