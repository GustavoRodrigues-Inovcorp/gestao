<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PlanoLog;
use Inertia\Inertia;

class LogsController extends Controller
{
    public function index()
    {
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;

        $activityLogs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get()
            ->map(fn($log) => [
                'id'          => 'activity-' . $log->id,
                'timestamp'   => $log->created_at->timestamp,
                'data'        => $log->created_at->format('d/m/Y'),
                'hora'        => $log->created_at->format('H:i:s'),
                'utilizador'  => $log->user?->name ?? 'Sistema',
                'menu'        => $log->menu,
                'acao'        => $log->acao,
                'detalhes'    => null,
                'ip'          => $log->ip ?? '—',
                'dispositivo' => $log->dispositivo ?? '—',
            ]);

        $billingLogs = $tenant
            ? PlanoLog::where('tenant_id', $tenant->id)
                ->with('user', 'planoAnterior', 'planoNovo')
                ->orderByDesc('created_at')
                ->limit(1000)
                ->get()
                ->map(fn($log) => [
                    'id'          => 'billing-' . $log->id,
                    'timestamp'   => $log->created_at->timestamp,
                    'data'        => $log->created_at->format('d/m/Y'),
                    'hora'        => $log->created_at->format('H:i:s'),
                    'utilizador'  => $log->user?->name ?? 'Sistema',
                    'menu'        => 'Planos e Subscrições',
                    'acao'        => $log->acao,
                    'detalhes'    => collect([
                        $log->planoAnterior?->nome ? 'Anterior: ' . $log->planoAnterior->nome : null,
                        $log->planoNovo?->nome ? 'Novo: ' . $log->planoNovo->nome : null,
                        $log->valor_pago !== null ? 'Valor: ' . number_format((float) $log->valor_pago, 2, ',', '.') . ' €' : null,
                        $log->notas,
                    ])->filter()->implode(' · '),
                    'ip'          => '—',
                    'dispositivo' => '—',
                ])
            : collect();

        return Inertia::render('Logs/Index', [
            'logs' => $activityLogs
                ->concat($billingLogs)
                ->sortByDesc(fn($log) => $log['timestamp'])
                ->values(),
        ]);
    }
}