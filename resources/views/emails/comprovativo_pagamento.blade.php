@component('mail::message')

Estimado(a) {{ $fornecedor->nome }},

Enviamos em anexo o comprovativo de pagamento da fatura **{{ $fatura->numero }}**.

Qualquer questão, entre em contacto connosco.

Cumprimentos,
{{ \App\Models\Empresa::first()?->nome ?? config('app.name') }}
@endcomponent