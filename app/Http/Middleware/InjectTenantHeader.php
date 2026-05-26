<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectTenantHeader
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (app()->has('current_tenant_id')) {
            $tenantId = app('current_tenant_id');
            // Adiciona header para chamadas externas e APIs
            $response->headers->set('X-Tenant-ID', $tenantId);
            // Assegura que o request tem o valor disponível
            $request->attributes->set('tenant_id', $tenantId);
        }

        return $response;
    }
}
