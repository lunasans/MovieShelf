@extends('layouts.saas')

@section('content')
<section class="pt-40 pb-32 px-8 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-2xl p-10 md:p-16 shadow-sm">
        
        <h1 class="text-4xl font-extrabold text-[#222222] mb-12">Datenschutz & Nutzungsbedingungen</h1>
        
        <div class="prose prose-gray max-w-none text-gray-600 space-y-8">
            
            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4">1. Nutzungsvertrag & Leistungsumfang</h2>
                <p>
                    MovieShelf Cloud wird als kostenfreie Plattform zur Verwaltung von digitalen und physischen Mediensammlungen ("Dienst") angeboten. Da dieser Dienst unentgeltlich (kostenlos) bereitgestellt wird, erfolgt die Nutzung auf eigene Gefahr. Es besteht kein Rechtsanspruch auf sofortigen Support, ständige Erreichbarkeit oder permanente Datensicherung. Wir behalten uns vor, den Dienst jederzeit einzuschränken, anzupassen oder vollständig einzustellen.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4">2. Haftungsbeschränkung</h2>
                <p>
                    Wir stellen die technische Plattform "as is" (wie gesehen) zur Verfügung. <br>
                    <strong>Wichtiger Hinweis:</strong> Es wird ausdrücklich empfohlen, lokale Backups über die Export-Funktion zu erstellen. Wir übernehmen keine Haftung für Datenverluste, Server-Ausfälle oder fehlerhafte Synchronisation, es sei denn, der Schaden wurde durch uns vorsätzlich oder grob fahrlässig verursacht.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4">3. Pflichten des Nutzers</h2>
                <p>
                    Du bist als Nutzer allein für die von dir eingetragenen Inhalte verantwortlich. Es ist strengstens untersagt, den Dienst für rechtswidrige Zwecke zu nutzen. Dazu gehört insbesondere:
                </p>
                <ul class="list-disc pl-6 mt-2 space-y-2">
                    <li>Das Hochladen von urheberrechtlich geschütztem Material (z.B. direkte Raubkopien von Filmen, sofern die Upload-Funktion existiert), an dem du keine Rechte besitzt.</li>
                    <li>Das automatisierte massenhafte Auslesen (Scraping) unserer Datenbank.</li>
                    <li>Die missbräuchliche Nutzung von API-Schnittstellen oder Sabotage der Serverinfrastruktur.</li>
                </ul>
                <p class="mt-4">
                    Bei Zuwiderhandlungen behalten wir uns das Recht vor, Accounts ohne Vorwarnung zu sperren oder permanent zu löschen.
                </p>
            </div>

            <hr class="border-gray-200 my-10">

            <div>
                <h2 class="text-2xl font-bold text-[#222222] mb-4">4. Datenschutzhinweise</h2>
                <p>
                    Wir nehmen den Schutz deiner Daten sehr ernst. Nachfolgend erklären wir, welche Daten erhoben werden und zu welchem Zweck.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-bold text-[#222222] mb-2">4.1 Erhobene Daten</h3>
                <p>
                    Wenn du dich für MovieShelf Cloud registrierst, speichern wir zur Bereitstellung deines logischen Cloud-Verzeichnisses folgende Daten:
                </p>
                <ul class="list-disc pl-6 mt-2 space-y-2">
                    <li>Deinen Vor- und Nachnamen</li>
                    <li>Deine E-Mail-Adresse (erforderlich für Login und Passwort-Wiederherstellung)</li>
                    <li>Dein verschlüsseltes Passwort (bcrypt-Hash, für uns nicht einsehbar)</li>
                    <li>Persönliche Identifikatoren wie deine <strong>Konto-ID</strong> (interne Datenbank-ID) und dein <strong>Kontoname</strong> (die von dir gewählte Wunsch-Subdomain, z.B. user.movieshelf.info) zur eindeutigen Zuordnung deines Accounts.</li>
                    <li>Sämtliche von dir aktiv in die Datenbank eingetragenen Filme, Tags, Bewertungen und Einstellungen.</li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-bold text-[#222222] mb-2">4.2 Zweck der Datenverarbeitung</h3>
                <p>
                    Die Datenverarbeitung erfolgt auf Basis von Art. 6 Abs. 1 lit. b DSGVO, um dir den Service (dein persönliches MovieShelf) technisch bereitstellen zu können. Wir nutzen deine E-Mail ausdrücklich <strong>nicht</strong> für den Versand von Werbe-Newslettern, sofern du nicht separat eingewilligt hast.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-bold text-[#222222] mb-2">4.3 Cookies & Technik</h3>
                <p>
                    Für die Funktion der Plattform nutzen wir sogenannte Session-Cookies. Diese sind technisch zwingend erforderlich (z.B. um dich nach dem Login im System angemeldet zu halten). Es werden keine Tracking-Cookies (wie Google Analytics) für das Nutzerverhalten auf den Subdomains eingesetzt.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-bold text-[#222222] mb-2">4.4 App-Informationen und Leistung (Google Play Richtlinien)</h3>
                <p>
                    Zur Sicherstellung der Stabilität und zur Fehlerbehebung erfassen wir, insbesondere bei der Nutzung unserer mobilen Anwendung (Android-App), technische Leistungsdaten. Diese Daten sind in der Regel anonymisiert und lassen sich nicht direkt auf deine Person zurückführen:
                </p>
                <ul class="list-disc pl-6 mt-2 space-y-2">
                    <li><strong>Absturzprotokolle (Crash Logs):</strong> Wenn die App oder der Server unerwartet beendet wird, erhalten wir einen automatisierten Fehlerbericht (z. B. Angaben zum Gerätetyp, Betriebssystemversion und den Code, der den Absturz verursacht hat), um den Fehler schnellstmöglich zu beheben.</li>
                    <li><strong>Diagnosedaten:</strong> Technische Informationen über Ladezeiten, Netzwerkfehler oder Speicherprobleme, um die App-Performance zu optimieren.</li>
                    <li><strong>Persönliche IDs in Protokollen:</strong> In bestimmten Fehlerfällen kann deine <strong>Konto-ID</strong> oder dein <strong>Kontoname</strong> an Absturzprotokolle angehängt werden, um ein spezifisches Problem deinem Account zuzuordnen und gezielt beheben zu können.</li>
                    <li><strong>Andere App-Leistungsdaten:</strong> Generelle Metriken zur Stabilität der App und API-Verbindungen.</li>
                </ul>
                <p class="mt-4">
                    Diese Datenerhebung erfolgt aufgrund unseres berechtigten Interesses (Art. 6 Abs. 1 lit. f DSGVO) an einer sicheren und fehlerfreien Bereitstellung des Dienstes. Wir teilen diese Rohdaten <strong>nicht</strong> mit Werbenetzwerken oder Dritt-Trackern.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-bold text-[#222222] mb-2">4.5 Deine Rechte</h3>
                <p>
                    Du hast jederzeit das Recht auf Auskunft über deine gespeicherten personenbezogenen Daten, deren Herkunft, Empfänger und den Zweck der Datenverarbeitung. Weiterhin hast du ein Recht auf Berichtigung, Sperrung oder <strong>Löschung</strong> dieser Daten.
                    <br><br>
                    <strong>Account löschen:</strong> Wenn du deinen Account löschst, werden deine assoziierte Subdomain und alle damit verbundenen Datenbank-Einträge unverzüglich aus unserem System entfernt.
                </p>
            </div>

        </div>
        
        <div class="mt-16 text-center">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#222222] text-white rounded-lg text-sm font-bold hover:bg-[#050505] transition-colors">
                <i class="bi bi-arrow-left"></i> Zurück zur Startseite
            </a>
        </div>

    </div>
</section>
@endsection
