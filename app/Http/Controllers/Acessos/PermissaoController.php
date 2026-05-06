<?php
namespace App\Http\Controllers\Acessos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->permissions) {
            foreach ($request->permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $role->syncPermissions($request->permissions);
        }

        return back();
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'ativo'       => ['boolean'],
        ]);

        $role->update(['name' => $request->name]);

        if ($request->permissions) {
            foreach ($request->permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return back();
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back();
    }
}