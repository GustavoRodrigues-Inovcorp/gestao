<?php
namespace App\Console\Commands;

use App\Models\Plano;
use App\Models\PlanoLog;
use App\Models\Subscricao;
use App\Services\SubscricaoService;
use Illuminate\Console\Command;

class ProcessarSubscricoes extends Command
{
    protected $signature   = 'subscricoes:processar';
    protected $description = 'Processa downgrades pendentes, trials expirados e cancelamentos agendados';

    public function handle(SubscricaoService $service): void
    {
        // Aplica downgrades pendentes
        $service->aplicarDowngradesPendentes();
        $this->info('Downgrades pendentes processados.');

        // Expira trials
        Subscricao::where('estado', 'trial')
            ->where('trial_fim', '<=', now())
            ->each(function ($sub) {
                $planoAnteriorId = $sub->plano_id;
                $planoFree = Plano::where('is_free', true)->first();
                $sub->update([
                    'estado'   => $planoFree ? 'ativa' : 'expirada',
                    'plano_id' => $planoFree?->id ?? $sub->plano_id,
                    'preco'    => 0,
                ]);

                PlanoLog::create([
                    'tenant_id'          => $sub->tenant_id,
                    'user_id'            => $sub->tenant->users()->first()?->id ?? 1,
                    'acao'               => 'trial_fim',
                    'plano_anterior_id'  => $planoAnteriorId,
                    'plano_novo_id'      => $planoFree?->id,
                    'notas'              => 'Trial expirado. Movido para plano Free.',
                ]);
            });

        $this->info('Trials expirados processados.');

        // Fecha subscricoes com cancelamento agendado quando atinge o proximo ciclo
        Subscricao::whereIn('estado', ['ativa', 'trial'])
            ->whereNotNull('cancelado_em')
            ->whereNotNull('proximo_ciclo')
            ->where('proximo_ciclo', '<=', now())
            ->each(function ($sub) {
                $sub->update([
                    'estado' => 'cancelada',
                    'fim'    => now(),
                ]);

                PlanoLog::create([
                    'tenant_id'         => $sub->tenant_id,
                    'user_id'           => $sub->tenant->users()->first()?->id ?? 1,
                    'acao'              => 'cancelamento_efetivado',
                    'plano_anterior_id' => $sub->plano_id,
                    'notas'             => 'Cancelamento efetivado no fim do ciclo.',
                ]);
            });

        $this->info('Cancelamentos agendados processados.');
    }
}