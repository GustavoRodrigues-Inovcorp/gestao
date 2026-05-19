<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $empresa = \App\Models\Empresa::first();
            $appTitle = $empresa?->nome ?? config('app.name', 'Laravel');
            $faviconUrl = $empresa?->logotipo
                ? route('empresa.logotipo') . '?v=' . ($empresa?->updated_at?->timestamp ?? now()->timestamp)
                : asset('favicon.ico');
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ $appTitle }}</title>
        <link rel="icon" type="image/*" href="{{ $faviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
