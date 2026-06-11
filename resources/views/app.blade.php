<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0F0F12">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        
        <title inertia>{{ config('app.name', 'CyberMarket') }}</title>

        <script>
            (function() {
                try {
                    const theme = localStorage.getItem('theme');
                    if (theme === 'dark' || !theme) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&display=swap" rel="stylesheet">

        @routes
        @vite(['resources/js/app.js', "resources/css/app.css"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-background text-foreground transition-colors duration-300">
        @inertia
    </body>
</html>