<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
            $empresa = $tenant
                ? \App\Models\Empresa::where('tenant_id', $tenant->id)->first()
                : \App\Models\Empresa::first();
            $appTitle = $empresa?->nome ?? $tenant?->nome ?? config('app.name', 'Laravel');
            $faviconUrl = $empresa?->logotipo
                ? route('empresa.logotipo') . '?v=' . ($empresa?->updated_at?->timestamp ?? now()->timestamp)
                : asset('favicon.ico');
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ $appTitle }}</title>
        <meta name="workspace-name" content="{{ $appTitle }}">
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
