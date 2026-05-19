<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; }
        .page { padding: 35px 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin-bottom: 6px; }
        .logo-box img { width: 100%; height: 100%; object-fit: contain; }
        .logo-nome { font-size: 14px; font-weight: 700; color: #111; text-transform: uppercase; letter-spacing: 1px; }
        .logo-sub { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 2px; margin-top: 2px; }
        .doc-tipo { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #111; text-align: right; }
        .doc-numero { font-size: 13px; color: #555; margin-top: 4px; text-align: right; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
        .dest-nome { font-size: 12px; font-weight: 700; margin-bottom: 3px; }
        .dest-detail { font-size: 9px; color: #555; line-height: 1.8; }
        .info-boxes { display: flex; gap: 0; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .info-box { flex: 1; padding: 8px 12px; border-right: 1px solid #e5e7eb; }
        .info-box:last-child { border-right: none; }
        .info-box-label { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 3px; }
        .info-box-value { font-size: 10px; font-weight: 600; color: #111; }
        .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .lines-table thead tr { background: #1a1a1a; color: #fff; }
        .lines-table thead th { padding: 8px 10px; text-align: left; font-size: 8.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .lines-table thead th.right { text-align: right; }
        .lines-table tbody tr { border-bottom: 1px solid #f3f4f6; }
        .lines-table tbody tr:nth-child(even) { background: #fafafa; }
        .lines-table tbody td { padding: 8px 10px; font-size: 9.5px; }
        .lines-table tbody td.right { text-align: right; }
        .lines-table tbody td.ref { color: #6b7280; font-size: 8.5px; }
        .totals-section { display: flex; justify-content: flex-end; margin-top: 6px; }
        .totals-box { width: 280px; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 9.5px; border-bottom: 1px solid #f3f4f6; }
        .total-row.grand { background: #1a1a1a; color: #fff; font-size: 11px; font-weight: 700; padding: 8px 12px; }
        .termos { margin-top: 24px; display: flex; gap: 30px; }
        .termos-title { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .termos-item { font-size: 9px; color: #444; line-height: 1.8; }
        .conta-item { font-size: 8.5px; color: #555; line-height: 1.8; }
        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
        .footer-left { font-size: 8px; color: #9ca3af; line-height: 1.8; }
        .footer-right { font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div>
            @if($empresa?->logotipo)
                <div class="logo-box">
                    <img src="{{ storage_path('app/private/' . $empresa->logotipo) }}" alt="Logo" />
                </div>
            @endif
            <div class="logo-nome">{{ $empresa?->nome ?? 'Empresa' }}</div>
            @if($empresa?->nif)<div class="logo-sub">NIF {{ $empresa->nif }}</div>@endif
        </div>
        <div>
            <div class="doc-tipo">Encomenda Fornecedor</div>
            <div class="doc-numero">Nº {{ str_pad($encomenda->numero, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</div>
        </div>
    </div>

    <hr class="divider">

    <div style="display: flex; gap: 40px; margin-bottom: 20px;">
        <div style="flex: 1;">
            <div style="font-size: 7.5px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px;">Emitente</div>
            <div class="dest-nome">{{ $empresa?->nome ?? '—' }}</div>
            <div class="dest-detail">
                @if($empresa?->morada){{ $empresa->morada }}<br>@endif
                @if($empresa?->codigo_postal){{ $empresa->codigo_postal }} {{ $empresa?->localidade }}<br>@endif
                @if($empresa?->nif)NIF: {{ $empresa->nif }}@endif
            </div>
        </div>
        <div style="flex: 1;">
            <div style="font-size: 7.5px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px;">Fornecedor</div>
            <div class="dest-nome">{{ $encomenda->fornecedor->nome }}</div>
            <div class="dest-detail">
                @if($encomenda->fornecedor->morada){{ $encomenda->fornecedor->morada }}<br>@endif
                @if($encomenda->fornecedor->codigo_postal){{ $encomenda->fornecedor->codigo_postal }} {{ $encomenda->fornecedor->localidade }}<br>@endif
                @if($encomenda->fornecedor->nif)NIF: {{ $encomenda->fornecedor->nif }}@endif
            </div>
        </div>
    </div>

    <div class="info-boxes">
        <div class="info-box">
            <div class="info-box-label">Nº Encomenda</div>
            <div class="info-box-value">{{ str_pad($encomenda->numero, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</div>
        </div>
        <div class="info-box">
            <div class="info-box-label">Data</div>
            <div class="info-box-value">{{ $encomenda->data?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="info-box">
            <div class="info-box-label">Válido até</div>
            <div class="info-box-value">{{ $encomenda->validade?->format('d/m/Y') ?? '—' }}</div>
        </div>
        @if($encomenda->fornecedor->nif)
        <div class="info-box">
            <div class="info-box-label">NIF Fornecedor</div>
            <div class="info-box-value">{{ $encomenda->fornecedor->nif }}</div>
        </div>
        @endif
    </div>

    <table class="lines-table">
        <thead>
            <tr>
                <th style="width: 8%;">Ref.</th>
                <th style="width: 42%;">Descrição</th>
                <th class="right" style="width: 8%;">Qtd.</th>
                <th class="right" style="width: 10%;">Un.</th>
                <th class="right" style="width: 12%;">Preço Unit.</th>
                <th class="right" style="width: 8%;">IVA</th>
                <th class="right" style="width: 12%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($encomenda->linhas as $linha)
            <tr>
                <td class="ref">{{ $linha->referencia ?? '—' }}</td>
                <td>{{ $linha->nome }}</td>
                <td class="right">{{ $linha->quantidade }}</td>
                <td class="right">Un</td>
                <td class="right">{{ number_format($linha->preco_custo ?? $linha->preco_compra ?? 0, 2, ',', '.') }} €</td>
                <td class="right">{{ $linha->iva }}%</td>
                <td class="right">{{ number_format($linha->subtotal, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subtotalSemIva = $encomenda->linhas->sum('subtotal');
        $totalIva = $encomenda->linhas->sum(fn($l) => $l->subtotal * $l->iva / 100);
        $total = $subtotalSemIva + $totalIva;
    @endphp

    <div class="totals-section">
        <div class="totals-box">
            <div class="total-row">
                <span>Subtotal sem IVA</span>
                <span>{{ number_format($subtotalSemIva, 2, ',', '.') }} €</span>
            </div>
            @foreach($encomenda->linhas->groupBy('iva') as $taxa => $linhas)
            <div class="total-row">
                <span>IVA {{ $taxa }}%</span>
                <span>{{ number_format($linhas->sum(fn($l) => $l->subtotal * $l->iva / 100), 2, ',', '.') }} €</span>
            </div>
            @endforeach
            <div class="total-row grand">
                <span>Total com IVA</span>
                <span>{{ number_format($total, 2, ',', '.') }} €</span>
            </div>
        </div>
    </div>

    <div class="termos">
        <div style="flex: 1;">
            <div class="termos-title">Termos e Condições</div>
            <div class="termos-item">Encomenda para fornecedor. Confirmar receção e prazos de entrega.</div>
        </div>
        @if(isset($contas) && count($contas) > 0)
        <div style="flex: 1;">
            <div class="termos-title">Dados Bancários</div>
            @foreach($contas as $conta)
            <div class="conta-item">IBAN {{ $conta->iban }} ({{ $conta->banco }})</div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="footer">
        <div class="footer-left">
            @if($empresa?->morada){{ $empresa->morada }} · @endif
            @if($empresa?->codigo_postal){{ $empresa->codigo_postal }} {{ $empresa?->localidade }} · @endif
            @if($empresa?->nif)NIF {{ $empresa->nif }}@endif
        </div>
        <div class="footer-right">Pág. 1</div>
    </div>

</div>
</body>
</html>