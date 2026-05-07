<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        .page { padding: 40px; }

        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .logo-area h1 { font-size: 22px; font-weight: 700; color: #111; }
        .logo-area p { font-size: 10px; color: #666; margin-top: 2px; }
        .doc-info { text-align: right; }
        .doc-info .doc-title { font-size: 20px; font-weight: 700; color: #111; text-transform: uppercase; letter-spacing: 2px; }
        .doc-info .doc-number { font-size: 13px; color: #555; margin-top: 4px; }

        /* Empresa + Cliente */
        .parties { display: flex; gap: 40px; margin-bottom: 30px; }
        .party { flex: 1; }
        .party-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 6px; }
        .party-name { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
        .party-detail { font-size: 10px; color: #555; line-height: 1.6; }

        /* Datas */
        .dates { display: flex; gap: 20px; margin-bottom: 30px; padding: 12px 16px; background: #f8f8f8; border-radius: 6px; }
        .date-item { flex: 1; }
        .date-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #999; }
        .date-value { font-size: 12px; font-weight: 600; margin-top: 2px; }

        /* Tabela */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #111; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #eee; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 9px 12px; font-size: 10px; }
        .text-right { text-align: right; }

        /* Totais */
        .totals { display: flex; justify-content: flex-end; margin-top: 10px; }
        .totals-box { width: 260px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; border-bottom: 1px solid #eee; }
        .total-row.grand { font-size: 14px; font-weight: 700; border-bottom: none; padding-top: 10px; }

        /* Estado */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .badge-rascunho { background: #f3f4f6; color: #6b7280; }
        .badge-fechado { background: #dcfce7; color: #166534; }

        /* Footer */
        .footer { margin-top: 50px; padding-top: 16px; border-top: 1px solid #eee; text-align: center; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="logo-area">
            <h1>{{ $empresa?->nome ?? 'Empresa' }}</h1>
            <p>{{ $empresa?->nif ? 'NIF: ' . $empresa->nif : '' }}</p>
            <p>{{ $empresa?->morada }}</p>
            <p>{{ $empresa?->codigo_postal }} {{ $empresa?->localidade }}</p>
        </div>
        <div class="doc-info">
            <div class="doc-title">Proposta</div>
            <div class="doc-number">Nº {{ str_pad($proposta->numero, 5, '0', STR_PAD_LEFT) }}</div>
            <div style="margin-top: 8px;">
                <span class="badge {{ $proposta->estado === 'fechado' ? 'badge-fechado' : 'badge-rascunho' }}">
                    {{ ucfirst($proposta->estado) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Empresa + Cliente -->
    <div class="parties">
        <div class="party">
            <div class="party-label">De</div>
            <div class="party-name">{{ $empresa?->nome ?? '—' }}</div>
            <div class="party-detail">
                {{ $empresa?->morada }}<br>
                {{ $empresa?->codigo_postal }} {{ $empresa?->localidade }}<br>
                {{ $empresa?->nif ? 'NIF: ' . $empresa->nif : '' }}
            </div>
        </div>
        <div class="party">
            <div class="party-label">Para</div>
            <div class="party-name">{{ $proposta->entidade->nome }}</div>
            <div class="party-detail">
                {{ $proposta->entidade->morada }}<br>
                {{ $proposta->entidade->codigo_postal }} {{ $proposta->entidade->localidade }}<br>
                {{ $proposta->entidade->nif ? 'NIF: ' . $proposta->entidade->nif : '' }}
            </div>
        </div>
    </div>

    <!-- Datas -->
    <div class="dates">
        <div class="date-item">
            <div class="date-label">Data</div>
            <div class="date-value">{{ $proposta->data?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="date-item">
            <div class="date-label">Validade</div>
            <div class="date-value">{{ $proposta->validade?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <!-- Linhas -->
    <table>
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Descrição</th>
                <th class="text-right">Qtd.</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proposta->linhas as $linha)
            <tr>
                <td>{{ $linha->referencia ?? '—' }}</td>
                <td>{{ $linha->nome }}</td>
                <td class="text-right">{{ $linha->quantidade }}</td>
                <td class="text-right">{{ number_format($linha->preco_venda, 2, ',', '.') }} €</td>
                <td class="text-right">{{ $linha->iva }}%</td>
                <td class="text-right">{{ number_format($linha->subtotal, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totais -->
    @php
        $subtotalSemIva = $proposta->linhas->sum('subtotal');
        $totalIva = $proposta->linhas->sum(fn($l) => $l->subtotal * $l->iva / 100);
        $total = $subtotalSemIva + $totalIva;
    @endphp
    <div class="totals">
        <div class="totals-box">
            <div class="total-row">
                <span>Subtotal</span>
                <span>{{ number_format($subtotalSemIva, 2, ',', '.') }} €</span>
            </div>
            <div class="total-row">
                <span>IVA</span>
                <span>{{ number_format($totalIva, 2, ',', '.') }} €</span>
            </div>
            <div class="total-row grand">
                <span>Total</span>
                <span>{{ number_format($total, 2, ',', '.') }} €</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $empresa?->nome }} &bull; {{ $empresa?->email }} &bull; {{ $empresa?->telefone }}
    </div>

</div>
</body>
</html>