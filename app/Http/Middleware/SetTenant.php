<?php
namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $tenantId = session('tenant_id');

        // Se não há tenant na sessão, usa o primeiro disponível
        if (!$tenantId) {
            $tenant = $user->tenants()->first();
            if ($tenant) {
                session(['tenant_id' => $tenant->id]);
                $tenantId = $tenant->id;
            }
        }

        if ($tenantId) {
            // Admins globais acedem a qualquer tenant
            if ($user->hasAnyRole(['admin', 'Administrador'])) {
                $tenant = Tenant::find($tenantId);
            } else {
                $tenant = $user->tenants()->where('tenants.id', $tenantId)->first();
            }

            if (!$tenant) {
                session()->forget('tenant_id');
                // Tenta o primeiro tenant disponível
                $tenant = $user->tenants()->first();
                if ($tenant) {
                    session(['tenant_id' => $tenant->id]);
                } else {
                    return redirect('/tenants')->withErrors(['tenant' => 'Cria ou junta-te a um workspace primeiro.']);
                }
            }

            app()->instance('current_tenant_id', $tenant->id);
            app()->instance('current_tenant', $tenant);
        }

        return $next($request);
    }
}