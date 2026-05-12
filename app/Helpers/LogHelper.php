<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class LogHelper
{
    public static function log(string $menu, string $acao): void
    {
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'menu'        => $menu,
                'acao'        => $acao,
                'ip'          => request()->ip(),
                'dispositivo' => self::parseDispositivo(request()->userAgent() ?? ''),
            ]);
        } catch (\Exception $e) {
            // Silencia erros de log para não interromper a aplicação
        }
    }

    private static function parseDispositivo(string $userAgent): string
    {
        if (!$userAgent) return '—';
        if (str_contains($userAgent, 'Mobile')) return 'Mobile';
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        return 'Desktop';
    }
}