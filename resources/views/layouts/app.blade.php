<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'LeMur · Crée un mur, partage le lien')</title>
        <meta name="description" content="@yield('description', 'Un tableau de post-it partagé que tu crées en un clic. Zéro compte, zéro installation : tu envoies le lien et tout le monde y colle ses notes.')">

        <meta property="og:site_name" content="LeMur">
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('og-title', 'LeMur · Crée un mur, partage le lien')">
        <meta property="og:description" content="@yield('description', 'Un tableau de post-it partagé que tu crées en un clic. Zéro compte, zéro installation : tu envoies le lien et tout le monde y colle ses notes.')">
        <meta property="og:url" content="@yield('og-url', url()->current())">
        <meta property="og:image" content="{{ asset('images/og-lemur.png') }}">
        <meta name="twitter:card" content="summary_large_image">
        @hasSection('no-index')
            <meta name="robots" content="noindex">
        @endif

        <link rel="icon" href="{{ asset('images/logo-icon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        @yield('content')

        <footer class="mt-16 border-t border-cork px-4 py-8">
            <div class="mx-auto flex max-w-5xl flex-col items-center gap-4 text-center sm:flex-row sm:justify-between sm:text-left">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="" class="h-6 w-6">
                    <p class="text-sm text-ink-alt">LeMur · gratuit, open source, zéro compte. Fait avec ❤️ pour les bandes de potes.</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ config('lemur.github_url') }}" target="_blank" rel="noopener"
                        class="text-sm font-medium text-ink-alt underline-offset-2 transition duration-200 ease-out hover:text-accent hover:underline">
                        Code source
                    </a>
                    <a href="{{ config('lemur.coffee_url') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                        ☕ Offre-nous un café
                    </a>
                </div>
            </div>
        </footer>
    </body>
</html>
