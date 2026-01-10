<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: true }" :class="{ 'light': !darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Axia') }} - AI Focus Coach</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-primary: #181a1c;
            --bg-secondary: #1f2123;
            --bg-tertiary: #232528;
            --bg-hover: #25272a;
            --border: #2c2f33;
            --text-primary: #fff;
            --text-secondary: #a0a0a0;
            --accent-pink: #e94b8c;
            --accent-green: #4caf50;
            --accent-yellow: #ffb74d;
            --accent-orange: #ff8a65;
        }

        :root.light {
            --bg-primary: #fff;
            --bg-secondary: #fafafa;
            --bg-tertiary: #f5f5f5;
            --bg-hover: #efefef;
            --border: #e5e5e5;
            --text-primary: #0a0a0a;
            --text-secondary: #737373;
        }

        * {
            border-color: var(--border);
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1 { font-size: 24px; font-weight: 500; line-height: 1.3; }
        h2 { font-size: 20px; font-weight: 500; line-height: 1.3; }
        h3 { font-size: 16px; font-weight: 500; line-height: 1.4; }
        p { color: var(--text-secondary); font-size: 14px; font-weight: 400; line-height: 1.5; }
        input, textarea, button { font-size: 14px; font-weight: 400; line-height: 1.5; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-tertiary); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-secondary); }
    </style>
</head>
<body class="antialiased">
    <div class="flex h-screen bg-[var(--bg-primary)]">
        @yield('content')
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
