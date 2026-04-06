@extends('layouts.saas')

@section('content')
<section class="pt-40 pb-32 px-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white border border-gray-200 rounded-2xl p-10 md:p-16 shadow-sm">
        
        <h1 class="text-4xl font-extrabold text-[#222222] mb-4">Konto löschen</h1>
        <p class="text-lg text-gray-500 mb-12">
            Hier erfährst du, wie du dein MovieShelf Cloud-Konto und alle damit verbundenen Daten dauerhaft und vollständig löschen kannst.
        </p>
        
        <div class="prose prose-gray max-w-none text-gray-600 space-y-10">
            
            <div class="p-6 bg-red-50 border border-red-100 rounded-xl relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                <h3 class="text-xl font-bold text-red-900 mb-2 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> Achtung: Dauerhafter Vorgang
                </h3>
                <p class="text-red-800 text-sm m-0">
                    Das Löschen deines Kontos kann <strong>nicht</strong> rückgängig gemacht werden. Alle deine eingetragenen Filme, Einstellungen und deine persönliche Subdomain unwiderruflich gelöscht.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4 border-b border-gray-100 pb-2">Wie kann ich mein Konto löschen?</h2>
                <p class="mb-4">Du hast zwei Möglichkeiten, die Löschung deines Kontos zu beantragen:</p>
                
                <h3 class="text-lg font-bold text-[#222222] mt-6 mb-2">Option A: Direkt in der App (Empfohlen)</h3>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Öffne MovieShelf (App oder Web) und melde dich an.</li>
                    <li>Navigiere in das Menü <strong>Einstellungen</strong> (Settings).</li>
                    <li>Scrolle nach unten zum Bereich <strong>Konto verwalten</strong> (Account Management).</li>
                    <li>Klicke auf den roten Button <strong>Konto endgültig löschen</strong> (Delete Account).</li>
                    <li>Bestätige den Vorgang durch die erneute Eingabe deines Passworts.</li>
                </ol>

                <h3 class="text-lg font-bold text-[#222222] mt-8 mb-2">Option B: Per E-Mail anfordern</h3>
                <p>
                    Wenn du keinen Zugriff mehr auf dein Konto hast, kannst du die Löschung per E-Mail anfordern:
                </p>
                <ol class="list-decimal pl-6 space-y-2 mt-2">
                    <li>Sende eine E-Mail an <strong>support@movieshelf.info</strong> <i>(Bitte verwende die E-Mail-Adresse, mit der du bei uns registriert bist)</i>.</li>
                    <li>Nutze den Betreff: <strong>Kontolöschung anfordern</strong>.</li>
                    <li>Wir werden deine Anfrage innerhalb von 3 Werktagen bearbeiten und bestätigen.</li>
                </ol>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4 border-b border-gray-100 pb-2">Welche Daten werden gelöscht?</h2>
                <p>Wenn die Löschung deines Kontos ausgeführt wird, werden folgende Daten aus unseren aktiven Systemen entfernt:</p>
                <ul class="list-disc pl-6 space-y-2 mt-4 text-sm">
                    <li><strong>Persönliche Daten:</strong> Dein Name, deine E-Mail-Adresse und dein Passwort-Hash.</li>
                    <li><strong>Nutzerinhalte:</strong> Deine gesamte Filmdatenbank, Bewertungen, Tags und hochgeladene Custom-Cover.</li>
                    <li><strong>Infrastruktur:</strong> Deine dedizierte MovieShelf-Subdomain wird freigegeben.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4 border-b border-gray-100 pb-2">Aufbewahrungsfristen (Retention)</h2>
                <p class="text-sm">
                    Sobald du die Kontolöschung bestätigst, werden deine Daten <strong>sofort und unwiderruflich</strong> aus den produktiven Datenbanken entfernt. <br><br>
                    <strong>Hinweis zu Backups:</strong> Aus Gründen der Datenintegrität und Systemsicherheit bewahren wir verschlüsselte, automatisierte Offline-Backups unseres gesamten Systems für maximal <strong>14 Tage</strong> auf. Nach Ablauf dieser 14 Tage sind deine Daten restlos und unwiederbringlich aus allen unseren Systemen (inklusive Backups) getilgt.
                </p>
            </div>

        </div>
        
        <div class="mt-16 text-center">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                <i class="bi bi-arrow-left"></i> Zurück zur Startseite
            </a>
        </div>

    </div>
</section>
@endsection
