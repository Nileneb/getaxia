<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Axia - AI Focus Coach for Founders</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="w-full border-b border-[var(--border-color)]">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <x-app-logo />

                @if (Route::has('login'))
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
                @endif
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="max-w-2xl w-full">
                <!-- Welcome Card -->
                <div
                    class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] mb-6 text-center">
                    <div class="flex justify-center mb-6">
                        <x-app-logo-icon class="w-12 h-12 text-lg" />
                    </div>
                    <h1 class="text-3xl font-medium text-[var(--text-primary)] mb-4">Welcome to Axia</h1>
                    <p class="text-lg text-[var(--text-secondary)] mb-2">Your AI Focus Coach</p>
                    <p class="text-[var(--text-secondary)]">
                        Axia helps startup founders prioritize what truly matters by analyzing your to-dos against your
                        goals using the 80/20 principle.
                    </p>
                </div>

                <!-- How it Works -->
                <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border-color)] mb-6">
                    <h2 class="text-xl font-medium text-[var(--text-primary)] mb-6">Here's how it works:</h2>

                    <div class="space-y-6">
                        <!-- Step 1 -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-[var(--text-primary)]">1</span>
                            </div>
                            <div>
                                <div class="text-[var(--text-primary)] font-medium mb-1">Add company info</div>
                                <div class="text-sm text-[var(--text-secondary)]">
                                    Tell us about your company, stage, and team size
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-[var(--text-primary)]">2</span>
                            </div>
                            <div>
                                <div class="text-[var(--text-primary)] font-medium mb-1">Define your goals</div>
                                <div class="text-sm text-[var(--text-secondary)]">
                                    Set your top priorities and what you want to achieve
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-[var(--text-primary)]">3</span>
                            </div>
                            <div>
                                <div class="text-[var(--text-primary)] font-medium mb-1">Add your current To-Dos</div>
                                <div class="text-sm text-[var(--text-secondary)]">
                                    Paste or upload your task list from any tool
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border-color)] flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-[var(--text-primary)]">4</span>
                            </div>
                            <div>
                                <div class="text-[var(--text-primary)] font-medium mb-1">Get your analysis</div>
                                <div class="text-sm text-[var(--text-secondary)]">
                                    Axia analyzes everything and shows you what truly matters
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 justify-center">
                    @auth
                        <flux:button href="{{ route('dashboard') }}" variant="primary">
                            Go to Dashboard
                        </flux:button>
                    @else
                        @if (Route::has('register'))
                            <flux:button href="{{ route('register') }}" variant="primary">
                                Start Setup
                            </flux:button>
                        @endif
                        <flux:button href="{{ route('login') }}" variant="outline">
                            Sign In
                        </flux:button>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-[var(--border-color)] py-6">
            <div class="max-w-6xl mx-auto px-6 flex items-center justify-between text-sm text-[var(--text-secondary)]">
                <span>© {{ date('Y') }} Axia. All rights reserved.</span>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-[var(--text-primary)] transition-colors">Privacy</a>
                    <a href="#" class="hover:text-[var(--text-primary)] transition-colors">Terms</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>