<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;

class LogsController extends Controller
{
    public function index()
    {
        return Inertia::render('Logs/Index', [
            'logs' => ActivityLog::with('user')
                ->orderByDesc('created_at')
                ->limit(1000)
                ->get()
                ->map(fn($log) => [
                    'id'         => $log->id,
                    'data'       => $log->created_at->format('d/m/Y'),
                    'hora'       => $log->created_at->format('H:i:s'),
                    'utilizador' => $log->user?->name ?? 'Sistema',
                    'menu'       => $log->menu,
                    'acao'       => $log->acao,
                    'ip'         => $log->ip ?? '—',
                    'dispositivo' => $log->dispositivo ?? '—',
                ]),
        ]);
    }
}