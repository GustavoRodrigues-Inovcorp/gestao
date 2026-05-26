<?php
namespace App\Http\Controllers\Acessos;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SubscricaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UtilizadorController extends Controller
{
    public function index()
    {
        return Inertia::render('Acessos/Utilizadores', [
            'utilizadores' => User::with('roles')->get()->map(function ($user) {
                return [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'phone'  => $user->phone,
                    'active' => $user->active,
                    'role'   => $user->roles->first()?->name,
                ];
            }),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        if ($tenant) {
            app(SubscricaoService::class)->validarLimiteCriacao($tenant, 'utilizadores');
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'role'     => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'active'   => true,
        ]);

        if ($request->role) {
            $user->assignRole($request->role);
        }

        LogHelper::log('Utilizadores', "Criou utilizador: {$user->name}");
        return back();
    }

    public function update(Request $request, User $utilizador)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'unique:users,email,' . $utilizador->id],
            'phone'  => ['nullable', 'string', 'max:20'],
            'active' => ['boolean'],
            'role'   => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $utilizador->update($request->only('name', 'email', 'phone', 'active'));

        if ($request->role) {
            $utilizador->syncRoles([$request->role]);
        } else {
            $utilizador->syncRoles([]);
        }

        LogHelper::log('Utilizadores', "Atualizou utilizador: {$utilizador->name}");
        return back();
    }

    public function destroy(User $utilizador)
    {
        LogHelper::log('Utilizadores', "Eliminou utilizador: {$utilizador->name}");
        $utilizador->delete();
        return back();
    }
}