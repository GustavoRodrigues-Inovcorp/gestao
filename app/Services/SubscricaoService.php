<?php
namespace App\Services;

use App\Models\Plano;
use App\Models\PlanoLog;
use App\Models\Subscricao;
use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

class SubscricaoService
{
    public function iniciarTrial(Tenant $tenant, ?Plano $plano = null): Subscricao
    {
        $plano ??= Plano::where('slug', 'starter')->first()
            ?? Plano::orderBy('preco_mensal')->where('trial_dias', '>', 0)->first();

        $subscricao = Subscricao::create([
            'tenant_id'    => $tenant->id,
            'plano_id'     => $plano->id,
            'estado'       => 'trial',
            'ciclo'        => 'mensal',
            'preco'        => 0,
            'trial_inicio' => now(),
            'trial_fim'    => now()->addDays($plano->trial_dias),
        ]);

        PlanoLog::create([
            'tenant_id'     => $tenant->id,
            'user_id'       => auth()->id(),
            'acao'          => 'trial_inicio',
            'plano_novo_id' => $plano->id,
            'notas'         => "Trial de {$plano->trial_dias} dias iniciado.",
        ]);

        return $subscricao;
    }

    public function upgrade(Tenant $tenant, Plano $novoPlano, string $ciclo = 'mensal'): Subscricao
    {
        $subscricao = $tenant->subscricaoAtiva;
        $planoAnterior = $subscricao?->plano;

        $preco = $ciclo === 'anual' ? $novoPlano->preco_anual : $novoPlano->preco_mensal;
        $prorrata = $subscricao ? $subscricao->calcularProrrata($novoPlano, $ciclo) : 0;

        if ($subscricao) {
            $subscricao->update([
                'plano_id'       => $novoPlano->id,
                'estado'         => 'ativa',
                'ciclo'          => $ciclo,
                'preco'          => $preco,
                'inicio'         => now(),
                'proximo_ciclo'  => $ciclo === 'anual' ? now()->addYear() : now()->addMonth(),
                'trial_fim'      => null,
                'plano_pendente_id' => null,
            ]);
        } else {
            $subscricao = Subscricao::create([
                'tenant_id'      => $tenant->id,
                'plano_id'       => $novoPlano->id,
                'estado'         => 'ativa',
                'ciclo'          => $ciclo,
                'preco'          => $preco,
                'inicio'         => now(),
                'proximo_ciclo'  => $ciclo === 'anual' ? now()->addYear() : now()->addMonth(),
            ]);
        }

        PlanoLog::create([
            'tenant_id'          => $tenant->id,
            'user_id'            => auth()->id(),
            'acao'               => 'upgrade',
            'plano_anterior_id'  => $planoAnterior?->id,
            'plano_novo_id'      => $novoPlano->id,
            'valor_pago'         => $prorrata,
            'notas'              => "Upgrade para {$novoPlano->nome}. Pró-rata: {$prorrata}€",
        ]);

        return $subscricao;
    }

    public function downgrade(Tenant $tenant, Plano $novoPlano, string $ciclo = 'mensal'): Subscricao
    {
        $subscricao = $tenant->subscricaoAtiva;
        if (!$subscricao) {
            return $this->upgrade($tenant, $novoPlano, $ciclo);
        }

        // Downgrade aplica-se no próximo ciclo
        $subscricao->update([
            'plano_pendente_id' => $novoPlano->id,
        ]);

        PlanoLog::create([
            'tenant_id'          => $tenant->id,
            'user_id'            => auth()->id(),
            'acao'               => 'downgrade',
            'plano_anterior_id'  => $subscricao->plano_id,
            'plano_novo_id'      => $novoPlano->id,
            'notas'              => "Downgrade agendado para {$novoPlano->nome} no próximo ciclo ({$subscricao->proximo_ciclo?->format('d/m/Y')}).",
        ]);

        return $subscricao;
    }

    public function cancelar(Tenant $tenant, string $motivo = ''): Subscricao
    {
        $subscricao = $tenant->subscricaoAtiva;
        if (!$subscricao) {
            $subscricao = $tenant->subscricao()->latest()->first();
        }
        if (!$subscricao) {
            throw new \RuntimeException('Sem subscricao para cancelar.');
        }

        $planoAnterior = $subscricao?->plano;

        // Cancela no fim do ciclo atual
        $subscricao->update([
            'cancelado_em'         => now(),
            'cancelamento_motivo'  => $motivo,
            'estado'               => in_array($subscricao->estado, ['trial', 'ativa'], true) ? $subscricao->estado : 'cancelada',
        ]);

        PlanoLog::create([
            'tenant_id'          => $tenant->id,
            'user_id'            => auth()->id(),
            'acao'               => 'cancelamento',
            'plano_anterior_id'  => $planoAnterior?->id,
            'notas'              => "Cancelamento. Motivo: {$motivo}. Acesso até: {$subscricao->proximo_ciclo?->format('d/m/Y')}",
        ]);

        return $subscricao;
    }

    public function verificarLimites(Tenant $tenant): array
    {
        $subscricao = $tenant->subscricaoAtiva;
        if (!$subscricao) return [];

        $plano = $subscricao->plano;

        $utilizadores = $tenant->users()->count();
        $clientes     = \App\Models\Entidade::semFiltroTenant()->where('tenant_id', $tenant->id)->where('is_cliente', true)->count();
        $documentos   = \App\Models\ArquivoDigital::semFiltroTenant()->where('tenant_id', $tenant->id)->count();

        return [
            'utilizadores' => [
                'atual'  => $utilizadores,
                'limite' => $plano->max_utilizadores,
                'ok'     => $plano->max_utilizadores === -1 || $utilizadores <= $plano->max_utilizadores,
            ],
            'clientes' => [
                'atual'  => $clientes,
                'limite' => $plano->max_clientes,
                'ok'     => $plano->max_clientes === -1 || $clientes <= $plano->max_clientes,
            ],
            'documentos' => [
                'atual'  => $documentos,
                'limite' => $plano->max_documentos,
                'ok'     => $plano->max_documentos === -1 || $documentos <= $plano->max_documentos,
            ],
        ];
    }

    public function validarLimiteCriacao(Tenant $tenant, string $recurso, int $quantidade = 1): void
    {
        $limites = $this->verificarLimites($tenant);

        if (!isset($limites[$recurso])) {
            return;
        }

        $limite = (int) ($limites[$recurso]['limite'] ?? -1);
        if ($limite === -1) {
            return;
        }

        $atual = (int) ($limites[$recurso]['atual'] ?? 0);
        if (($atual + $quantidade) <= $limite) {
            return;
        }

        throw ValidationException::withMessages([
            'limite' => $this->mensagemLimite($recurso, $limite),
        ]);
    }

    private function mensagemLimite(string $recurso, int $limite): string
    {
        return match ($recurso) {
            'utilizadores' => "O seu plano permite no máximo {$limite} utilizadores.",
            'clientes' => "O seu plano permite no máximo {$limite} clientes.",
            'documentos' => "O seu plano permite no máximo {$limite} documentos.",
            default => 'O seu plano atingiu o limite de utilização.',
        };
    }

    public function aplicarDowngradesPendentes(): void
    {
        Subscricao::where('estado', 'ativa')
            ->whereNotNull('plano_pendente_id')
            ->where('proximo_ciclo', '<=', now())
            ->each(function ($sub) {
                $planoNovo = $sub->planoPendente;
                $sub->update([
                    'plano_id'          => $planoNovo->id,
                    'plano_pendente_id' => null,
                    'preco'             => $sub->ciclo === 'anual' ? $planoNovo->preco_anual : $planoNovo->preco_mensal,
                    'proximo_ciclo'     => $sub->ciclo === 'anual' ? now()->addYear() : now()->addMonth(),
                ]);
            });
    }
}