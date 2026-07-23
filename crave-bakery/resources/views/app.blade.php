<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @php
            $themeService = app(\App\Services\SiteSettingService::class);
            $themeCss = $themeService->themeCssProperties();
            $themeFontsHref = $themeService->googleFontsHref();
        @endphp

        <style>
            :root {
                @foreach ($themeCss as $property => $value)
                    {{ $property }}: {{ $value }};
                @endforeach
            }
        </style>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link id="app-theme-fonts" href="{{ $themeFontsHref }}" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
