<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impressum - Axia</title>

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
                <flux:heading size="xl" class="mb-8">Impressum</flux:heading>

                <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] space-y-6">
                    <div>
                        <flux:heading size="lg" class="mb-2">Angaben gemäß § 5 TMG</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Vertreten durch</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Lorem Ipsum<br>
                            Musterstraße 123<br>
                            12345 Musterstadt<br>
                            Deutschland
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Kontakt</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Telefon: +49 (0) 123 456789<br>
                            E-Mail: info@example.com
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Registereintrag</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Eintragung im Handelsregister.<br>
                            Registergericht: Amtsgericht Musterstadt<br>
                            Registernummer: HRB 12345
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Umsatzsteuer-ID</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Umsatzsteuer-Identifikationsnummer gemäß § 27a Umsatzsteuergesetz:<br>
                            DE 123 456 789
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Lorem Ipsum<br>
                            Musterstraße 123<br>
                            12345 Musterstadt
                        </p>
                    </div>

                    <div>
                        <flux:heading size="lg" class="mb-2">Haftungsausschluss</flux:heading>
                        <p class="text-[var(--text-secondary)]">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        </p>
                        <p class="text-[var(--text-secondary)] mt-2">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-[var(--border-color)] py-6">
            <div class="max-w-6xl mx-auto px-6 flex items-center justify-between text-sm text-[var(--text-secondary)]">
                <span>&copy; {{ date('Y') }} Axia. All rights reserved.</span>
                <div class="flex gap-6">
                    <a href="{{ route('datenschutz') }}" class="hover:text-[var(--text-primary)] transition-colors">Datenschutz</a>
                    <a href="{{ route('impressum') }}" class="hover:text-[var(--text-primary)] transition-colors font-medium text-[var(--text-primary)]">Impressum</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
