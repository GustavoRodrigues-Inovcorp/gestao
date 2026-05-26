<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tenants (Plain)</title>
    <style>body{font-family:Inter,system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:24px;background:#0f1724;color:#e6eef8}button{padding:8px 12px;border-radius:6px;border:0;background:#0ea5a9;color:#042f2f;cursor:pointer}pre{background:#071029;padding:12px;border-radius:6px;}</style>
</head>
<body>
    <h1>Tenants (Plain)</h1>
    <p>Tenant activo: <strong>{{ $sessionTenant ?? 'nenhum' }}</strong></p>

    <ul>
        @foreach($tenants as $t)
            <li style="margin-bottom:12px;">
                <strong>{{ $t->nome }}</strong> (id: {{ $t->id }})
                @if(session('tenant_id') == $t->id)
                    <em> — activo</em>
                @else
                    <form method="post" action="/tenants/{{ $t->id }}/switch" style="display:inline">
                        @csrf
                        <button type="submit">Switch para este tenant</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>

    <p><a href="/dashboard" style="color:#9aa3b2">Voltar ao dashboard</a></p>
</body>
</html>