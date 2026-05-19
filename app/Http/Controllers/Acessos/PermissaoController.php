<?php
namespace App\Http\Controllers\Acessos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissaoController extends Controller
{
    protected $menus = [
        'clientes', 'fornecedores', 'contactos', 'propostas',
        'calendario', 'encomendas_clientes', 'encomendas_fornecedores',
        'ordens_trabalho', 'financeiro', 'arquivo_digital',
        'utilizadores', 'permissoes', 'configuracoes',
    ];

    protected $menuLabels = [
        'clientes'                 => 'Clientes',
        'fornecedores'             => 'Fornecedores',
        'contactos'                => 'Contactos',
        'propostas'                => 'Propostas',
        'calendario'               => 'Calendário',
        'encomendas_clientes'      => 'Encomendas - Clientes',
        'encomendas_fornecedores'  => 'Encomendas - Fornecedores',
        'ordens_trabalho'          => 'Ordens de Trabalho',
        'financeiro'               => 'Financeiro',
        'arquivo_digital'          => 'Arquivo Digital',
        'utilizadores'             => 'Utilizadores',
        'permissoes'               => 'Permissões',
        'configuracoes'            => 'Configurações',
    ];

    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->get()->map(function ($role) {
            return [
                'id'          => $role->id,
                'name'        => $role->name,
                'ativo'       => $role->ativo ?? true,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users->count(),
            ];
        });

        return Inertia::render('Acessos/Permissoes', [
            'roles'       => $roles,
            'menus'       => $this->menus,
            'menuLabels'  => $this->menuLabels,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $payload = ['name' => $request->name, 'guard_name' => 'web'];
        if (Schema::hasColumn('roles', 'ativo')) {
            $payload['ativo'] = $request->boolean('ativo', true);
        }

        $role = Role::create($payload);

        $permissions = collect($request->input('permissions', []))
            ->filter(fn($perm) => is_string($perm) && $perm !== '')
            ->values()
            ->all();

        if (!empty($permissions)) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back();
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'ativo'       => ['boolean'],
        ]);

        $updatePayload = ['name' => $request->name];
        if (Schema::hasColumn('roles', 'ativo')) {
            $updatePayload['ativo'] = $request->boolean('ativo', true);
        }
        $role->update($updatePayload);

        $permissions = collect($request->input('permissions', []))
            ->filter(fn($perm) => is_string($perm) && $perm !== '')
            ->values()
            ->all();

        if (!empty($permissions)) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back();
    }

    public function destroy(Role $role)
    {
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return back();
    }
}