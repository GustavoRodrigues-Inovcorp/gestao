@component('mail::message')

Olá,

O teu trial do plano **{{ $plano->nome }}** termina em **{{ $dias }} dia(s)**.

Se quiseres manter o acesso sem interrupções, entra na área de planos e escolhe uma subscrição ativa antes da data de fim.

Data de fim do trial: **{{ optional($subscricao->trial_fim)->format('d/m/Y') }}**

Cumprimentos,
{{ \App\Models\Empresa::first()?->nome ?? config('app.name') }}
@endcomponent