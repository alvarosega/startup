<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0B1221">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        
        <title inertia>{{ config('app.name', 'CyberMarket') }}</title>

        <script>
            (function() {
                try {
                    const theme = localStorage.getItem('theme');
                    // REGLA: Si no hay nada o es 'dark', inyectamos la clase inmediatamente
                    if (theme === 'dark' || !theme) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.js', "resources/css/app.css"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-background text-foreground transition-colors duration-300">
        @inertia
    </body>
</html>