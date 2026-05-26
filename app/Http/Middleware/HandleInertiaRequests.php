<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user'     => $user,
                'roles'    => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'is_admin' => $user ? $user->hasAnyRole(['admin', 'Administrador']) : false,
            ],
            'empresa' => fn() => $tenant
                ? \App\Models\Empresa::where('tenant_id', $tenant->id)->first()?->only(['nome', 'logotipo', 'updated_at'])
                : null,
            'tenant_atual' => fn() => $tenant ? [
                'id'   => $tenant->id,
                'nome' => $tenant->nome,
                'slug' => $tenant->slug,
            ] : null,
            'tenants' => fn() => $user
                ? $user->tenants()->get()->map(fn($t) => [
                    'id'    => $t->id,
                    'nome'  => $t->nome,
                    'slug'  => $t->slug,
                    'ativo' => session('tenant_id') == $t->id,
                ])
                : [],
        ];
    }
}