<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Datenschutzerklärung - Axia</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="w-full border-b border-[var(--border-color)]">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}">
                    <x-app-logo />
                </a>

                <nav class="flex items-center gap-3">
                    @auth
                        <flux:button href="{{ url('/dashboard') }}" variant="primary">
                            Dashboard
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="ghost">
                            Log in
                        </flux:button>
                        @if (Route::has('register'))
                            <flux:button href="{{ route('register') }}" variant="primary">
                                Get Started
                            </flux:button>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-6 py-12">
            <div class="max-w-3xl mx-auto">
                <flux:heading size="xl" class="mb-8">Datenschutzerklärung</flux:heading>

                <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] space-y-8">

                    {{-- 1. Verantwortlicher --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">1. Verantwortlicher</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Verantwortlicher im Sinne der Datenschutz-Grundverordnung (DSGVO) ist der im
                            <a href="{{ route('impressum') }}" class="underline hover:text-[var(--text-primary)]">Impressum</a>
                            genannte Betreiber dieser Website.
                        </p>
                    </section>

                    {{-- 2. Erhebung und Speicherung personenbezogener Daten --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">2. Erhebung und Speicherung personenbezogener Daten</flux:heading>
                        <p class="text-[var(--text-secondary)] mb-2">
                            Beim Besuch unserer Website werden automatisch folgende Informationen erfasst:
                        </p>
                        <ul class="list-disc list-inside text-[var(--text-secondary)] space-y-1">
                            <li>IP-Adresse des anfragenden Rechners</li>
                            <li>Datum und Uhrzeit des Zugriffs</li>
                            <li>Name und URL der abgerufenen Datei</li>
                            <li>Website, von der aus der Zugriff erfolgt (Referrer-URL)</li>
                            <li>Verwendeter Browser und ggf. Betriebssystem sowie der Name Ihres Access-Providers</li>
                        </ul>
                        <p class="text-[var(--text-secondary)] mt-2">
                            Diese Daten werden ausschließlich zur Sicherstellung eines störungsfreien Betriebs der Website und zur Verbesserung unseres Angebots ausgewertet. Eine Zuordnung zu einer bestimmten Person ist nicht möglich. Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen.
                        </p>
                    </section>

                    {{-- 3. Registrierung und Nutzerkonto --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">3. Registrierung und Nutzerkonto</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Bei der Registrierung erheben wir folgende Daten: Name und E-Mail-Adresse. Diese Daten werden zur Bereitstellung Ihres Nutzerkontos, zur Authentifizierung und zur Erbringung unserer Dienstleistungen verarbeitet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (Vertragserfüllung). Ihre Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr erforderlich sind oder Sie Ihr Konto löschen.
                        </p>
                    </section>

                    {{-- 4. KI-gestützte Analyse --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">4. KI-gestützte Analyse</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Axia nutzt KI-Dienste (Large Language Models) zur Analyse Ihrer eingegebenen Aufgaben im Kontext Ihrer Unternehmensziele. Dabei werden die von Ihnen bereitgestellten Informationen (Aufgaben, Ziele, KPIs) an einen externen KI-Dienstleister (API) übermittelt, um die Analyse durchzuführen. Die Verarbeitung erfolgt auf Grundlage Ihrer Einwilligung gemäß Art. 6 Abs. 1 lit. a DSGVO. Sie können die Einwilligung jederzeit widerrufen, indem Sie den Dienst nicht mehr nutzen.
                        </p>
                    </section>

                    {{-- 5. Zahlungsabwicklung --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">5. Zahlungsabwicklung</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Für die Zahlungsabwicklung nutzen wir den Dienst Stripe (Stripe Inc., 354 Oyster Point Blvd, South San Francisco, CA 94080, USA). Bei kostenpflichtigen Abonnements werden Zahlungsdaten direkt von Stripe verarbeitet. Wir selbst speichern keine Kreditkarten- oder Bankdaten. Weitere Informationen finden Sie in der
                            <a href="https://stripe.com/de/privacy" target="_blank" rel="noopener noreferrer" class="underline hover:text-[var(--text-primary)]">Datenschutzerklärung von Stripe</a>.
                        </p>
                    </section>

                    {{-- 6. Cookies --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">6. Cookies</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Unsere Website verwendet technisch notwendige Cookies, die für den Betrieb der Seite erforderlich sind (z.&nbsp;B. Session-Cookies für die Authentifizierung). Diese Cookies werden nach Ende Ihrer Browser-Sitzung oder nach Ablauf der Session-Dauer automatisch gelöscht. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse). Tracking- oder Marketing-Cookies werden nicht eingesetzt.
                        </p>
                    </section>

                    {{-- 7. Ihre Rechte --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">7. Ihre Rechte</flux:heading>
                        <p class="text-[var(--text-secondary)] mb-2">
                            Sie haben gegenüber uns folgende Rechte hinsichtlich der Sie betreffenden personenbezogenen Daten:
                        </p>
                        <ul class="list-disc list-inside text-[var(--text-secondary)] space-y-1">
                            <li>Recht auf Auskunft (Art. 15 DSGVO)</li>
                            <li>Recht auf Berichtigung (Art. 16 DSGVO)</li>
                            <li>Recht auf Löschung (Art. 17 DSGVO)</li>
                            <li>Recht auf Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
                            <li>Recht auf Datenübertragbarkeit (Art. 20 DSGVO)</li>
                            <li>Recht auf Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
                        </ul>
                        <p class="text-[var(--text-secondary)] mt-2">
                            Zudem haben Sie das Recht, sich bei einer Datenschutz-Aufsichtsbehörde über die Verarbeitung Ihrer personenbezogenen Daten zu beschweren.
                        </p>
                    </section>

                    {{-- 8. Datensicherheit --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">8. Datensicherheit</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Wir verwenden innerhalb des Website-Besuchs das verbreitete SSL-/TLS-Verfahren in Verbindung mit der jeweils höchsten Verschlüsselungsstufe, die von Ihrem Browser unterstützt wird. Sämtliche Passwörter werden gehasht gespeichert. Wir bedienen uns geeigneter technischer und organisatorischer Sicherheitsmaßnahmen, um Ihre Daten gegen zufällige oder vorsätzliche Manipulationen, Verlust, Zerstörung oder den Zugriff unberechtigter Personen zu schützen.
                        </p>
                    </section>

                    {{-- 9. Änderung der Datenschutzerklärung --}}
                    <section>
                        <flux:heading size="lg" class="mb-2">9. Änderung dieser Datenschutzerklärung</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den aktuellen rechtlichen Anforderungen entspricht oder um Änderungen unserer Leistungen umzusetzen. Für Ihren erneuten Besuch gilt dann die neue Datenschutzerklärung.
                        </p>
                    </section>

                    <p class="text-sm text-[var(--text-secondary)]">Stand: {{ \Carbon\Carbon::now()->locale('de')->isoFormat('MMMM YYYY') }}</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-[var(--border-color)] py-6">
            <div class="max-w-6xl mx-auto px-6 flex items-center justify-between text-sm text-[var(--text-secondary)]">
                <span>&copy; {{ date('Y') }} Axia. All rights reserved.</span>
                <div class="flex gap-6">
                    <a href="{{ route('datenschutz') }}" class="hover:text-[var(--text-primary)] transition-colors font-medium text-[var(--text-primary)]">Datenschutz</a>
                    <a href="{{ route('impressum') }}" class="hover:text-[var(--text-primary)] transition-colors">Impressum</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
