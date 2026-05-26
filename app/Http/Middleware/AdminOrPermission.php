<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = $request->user();
        $isAdmin = $user?->hasAnyRole(['admin', 'Administrador']) ?? false;

        if ($isAdmin) {
            return $next($request);
        }

        // Delegate to Spatie Permission middleware for normal checks
        $middleware = new \Spatie\Permission\Middleware\PermissionMiddleware();
        return $middleware->handle($request, $next, ...$permissions);
    }
}
