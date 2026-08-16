<!DOCTYPE html>
<html lang="de" class="bg-gray-950 text-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zugriff erlauben · Movieshelf</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <p class="text-2xl font-bold mb-1">
                <span class="text-amber-400">Movieshelf</span>
            </p>
            <p class="text-gray-400 text-sm">Autorisierungsanfrage</p>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-amber-400/10 border border-amber-400/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-amber-400">{{ substr($client->name, 0, 1) }}</span>
                </div>
                <h1 class="text-lg font-semibold mb-1">{{ $client->name }}</h1>
                <p class="text-sm text-gray-400">
                    möchte auf dein Movieshelf-Konto zugreifen
                </p>
            </div>

            <div class="bg-gray-800 rounded-xl p-4 mb-6 space-y-2 text-sm text-gray-300">
                <p class="flex items-center gap-2">
                    <span class="text-green-400">✓</span> Name und Benutzername
                </p>
                <p class="flex items-center gap-2">
                    <span class="text-green-400">✓</span> E-Mail-Adresse
                </p>
            </div>

            <div class="text-center mb-6">
                <p class="text-xs text-gray-500">
                    Angemeldet als <span class="text-gray-300">{{ auth()->user()->email }}</span>
                </p>
            </div>

            <div class="flex gap-3">
                <form method="POST" action="/oauth/authorize" class="flex-1">
                    @csrf
                    <input type="hidden" name="client_id"             value="{{ $client->client_id }}">
                    <input type="hidden" name="redirect_uri"          value="{{ $redirect_uri }}">
                    <input type="hidden" name="state"                  value="{{ $state }}">
                    <input type="hidden" name="approved"               value="0">
                    @if($code_challenge)
                    <input type="hidden" name="code_challenge"         value="{{ $code_challenge }}">
                    <input type="hidden" name="code_challenge_method"  value="{{ $code_challenge_method }}">
                    @endif
                    <button type="submit" class="w-full border border-gray-600 text-gray-300 py-2.5 rounded-xl hover:bg-gray-800 transition-colors text-sm">
                        Ablehnen
                    </button>
                </form>
                <form method="POST" action="/oauth/authorize" class="flex-1">
                    @csrf
                    <input type="hidden" name="client_id"             value="{{ $client->client_id }}">
                    <input type="hidden" name="redirect_uri"          value="{{ $redirect_uri }}">
                    <input type="hidden" name="state"                  value="{{ $state }}">
                    <input type="hidden" name="approved"               value="1">
                    @if($code_challenge)
                    <input type="hidden" name="code_challenge"         value="{{ $code_challenge }}">
                    <input type="hidden" name="code_challenge_method"  value="{{ $code_challenge_method }}">
                    @endif
                    <button type="submit" class="w-full bg-amber-400 text-gray-950 font-semibold py-2.5 rounded-xl hover:bg-amber-300 transition-colors text-sm">
                        Erlauben
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
