<!DOCTYPE html>
<html lang="de" class="bg-gray-950 text-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldung abgeschlossen · Movieshelf</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm text-center">
        <p class="text-2xl font-bold mb-8">
            <span class="text-amber-400">Movieshelf</span>
        </p>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="w-16 h-16 bg-green-400/10 border border-green-400/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl text-green-400">✓</span>
            </div>
            <h1 class="text-lg font-semibold mb-2">Anmeldung erfolgreich</h1>
            <p class="text-sm text-gray-400 mb-6">Die App wird jetzt geöffnet. Du kannst diesen Tab danach schließen.</p>
            <p class="text-xs text-gray-600">Falls die App nicht automatisch öffnet, klicke auf den Link unten.</p>
            <a id="deeplink" href="#" class="mt-3 inline-block text-xs text-amber-400 underline">App manuell öffnen</a>
        </div>
    </div>

    <script>
        const url = @json($callbackUrl);
        document.getElementById('deeplink').href = url;
        window.location.href = url;
    </script>
</body>
</html>
