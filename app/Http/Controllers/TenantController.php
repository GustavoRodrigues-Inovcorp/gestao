<?php
namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasAnyRole(['admin', 'Administrador']);
        $query = ($isAdmin ? Tenant::query() : $user->tenants())->with('empresa');

        return Inertia::render('Tenants/Index', [
            'tenants' => $query
                ->get()
                ->map(fn($t) => [
                    'id'      => $t->id,
                    'nome'    => $t->nome,
                    'slug'    => $t->slug,
                    'estado'  => $t->estado,
                    'logotipo' => $t->empresa ? $t->empresa->logotipo : null,
                    'tem_logotipo' => !empty($t->empresa && $t->empresa->logotipo),
                    'ativo'   => session('tenant_id') === $t->id,
                    'onboarding_completo' => isset($t->config['onboarding']['completed']) && $t->config['onboarding']['completed'],
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Apenas administradores podem criar workspaces
        abort_unless($user->hasAnyRole(['admin', 'Administrador']), 403, 'Sem permissão para criar workspaces.');

        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $tenant = Tenant::create([
            'nome'   => $request->nome,
            'slug'   => Str::slug($request->nome) . '-' . Str::random(6),
            'estado' => 'ativo',
        ]);

        \App\Models\Empresa::create([
            'tenant_id' => $tenant->id,
            'nome'      => $request->nome,
        ]);

        // Set session to the new tenant so admin can configure it immediately
        session(['tenant_id' => $tenant->id]);

        return redirect('/onboarding')->with('success', 'Workspace criado!');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $user = auth()->user();

        // Apenas administradores podem editar workspaces
        abort_unless($user->hasAnyRole(['admin', 'Administrador']), 403, 'Sem permissão para editar workspaces.');

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'in:ativo,inativo,suspenso'],
            'logotipo' => ['nullable', 'image', 'max:2048'],
        ]);

        $slug = Str::slug($data['slug']);
        if ($slug === '') {
            return back()->withErrors(['slug' => 'Slug inválido.']);
        }

        $slugExists = Tenant::where('slug', $slug)
            ->whereKeyNot($tenant->id)
            ->exists();

        if ($slugExists) {
            return back()->withErrors(['slug' => 'Slug já está em uso.']);
        }

        $tenant->update([
            'nome' => $data['nome'],
            'slug' => $slug,
            'estado' => $data['estado'],
        ]);

        $empresa = $tenant->empresa()->firstOrCreate([
            'tenant_id' => $tenant->id,
        ], [
            'nome' => $data['nome'],
        ]);

        $empresa->nome = $data['nome'];

        if ($request->hasFile('logotipo')) {
            if ($empresa->logotipo) {
                Storage::disk('local')->delete($empresa->logotipo);
            }

            $empresa->logotipo = $request->file('logotipo')->store('empresa', 'local');
        }

        $empresa->save();

        return back()->with('success', 'Workspace atualizado!');
    }

    public function switch(Request $request, Tenant $tenant)
    {
        $user = auth()->user();
        $hasAccess = $user->hasAnyRole(['admin', 'Administrador']) || $user->tenants()->where('tenants.id', $tenant->id)->exists();
        abort_unless($hasAccess, 403);

        session(['tenant_id' => $tenant->id]);

        // If this is an Inertia request, instruct the client to visit /dashboard
        if ($request->header('X-Inertia')) {
            return Inertia::location('/dashboard');
        }

        return redirect('/dashboard')->with('success', "Switched para {$tenant->nome}");
    }

    public function preferences(Request $request, Tenant $tenant)
    {
        $user = auth()->user();

        // Apenas administradores podem alterar preferências dos workspaces
        abort_unless($user->hasAnyRole(['admin', 'Administrator','Administrador']), 403);

        $data = $request->validate([
            'config' => ['nullable', 'array'],
        ]);

        $input = $data['config'] ?? [];

        // Allowed and normalized keys for this app
        $normalized = [];
        $errors = [];

        // Tema: aceita 'escuro'/'dark' => 'dark', 'claro'/'light' => 'light'
        if (array_key_exists('tema', $input)) {
            $t = strtolower((string) $input['tema']);
            if (in_array($t, ['escuro', 'dark'])) $normalized['tema'] = 'dark';
            elseif (in_array($t, ['claro', 'light'])) $normalized['tema'] = 'light';
            else $errors['tema'] = 'Tema inválido (usar escuro|claro).';
        }

        // Moeda: ISO code, normalizar para 3 letras maiúsculas
        if (array_key_exists('moeda', $input)) {
            $m = strtoupper(trim((string) $input['moeda']));
            $m = preg_replace('/[^A-Z]/', '', $m);
            if ($m === '' || strlen($m) > 5) {
                $errors['moeda'] = 'Código de moeda inválido.';
            } else {
                $normalized['moeda'] = substr($m, 0, 3);
            }
        }

        // Mostrar avanços: boolean
        if (array_key_exists('mostrar_avancos', $input)) {
            $normalized['mostrar_avancos'] = filter_var($input['mostrar_avancos'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($normalized['mostrar_avancos'])) {
                $errors['mostrar_avancos'] = 'Valor inválido para mostrar_avancos (true/false).';
            }
        }

        // Fuso horário: validar contra lista de timezones
        if (array_key_exists('fuso_horario', $input)) {
            $tz = (string) $input['fuso_horario'];
            if (in_array($tz, \DateTimeZone::listIdentifiers())) {
                $normalized['fuso_horario'] = $tz;
            } else {
                $errors['fuso_horario'] = 'Fuso horário inválido.';
            }
        }

        // Prefixo de faturas (opcional): limitar e normalizar
        if (array_key_exists('invoice_prefix', $input)) {
            $p = preg_replace('/[^A-Za-z0-9-_]/', '', (string) $input['invoice_prefix']);
            $normalized['invoice_prefix'] = substr($p, 0, 10);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        $tenant->config = $normalized;
        $tenant->save();

        return back()->with('success', 'Preferências guardadas.');
    }

    public function destroy(Tenant $tenant)
    {
        // Apenas administradores podem remover workspaces
        abort_unless(auth()->user()->hasAnyRole(['admin', 'Administrator','Administrador']), 403);
        $tenant->delete();
        session()->forget('tenant_id');
        return redirect('/');
    }

    /**
     * Invite users to the tenant by email. If user exists, attach; otherwise create a placeholder user.
     */
    public function invite(Request $request, Tenant $tenant)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'Administrator','Administrador']), 403);

        $data = $request->validate([
            'emails' => ['required', 'array'],
            'emails.*' => ['email'],
            'role' => ['nullable', 'string'],
        ]);

        $toAttach = [];
        foreach ($data['emails'] as $email) {
            $existing = \App\Models\User::where('email', $email)->first();
            if (!$existing || !$tenant->users()->where('user_id', $existing->id)->exists()) {
                $toAttach[] = $email;
            }
        }

        if (!empty($toAttach)) {
            app(\App\Services\SubscricaoService::class)->validarLimiteCriacao($tenant, 'utilizadores', count($toAttach));
        }

        $attached = [];

        foreach ($data['emails'] as $email) {
            $existing = \App\Models\User::where('email', $email)->first();
            if (!$existing) {
                $existing = \App\Models\User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => bcrypt(bin2hex(random_bytes(8))),
                ]);
            }

            // Attach if not attached
            if (!$tenant->users()->where('user_id', $existing->id)->exists()) {
                $tenant->users()->attach($existing->id);
            }

            $attached[] = $existing->email;
        }

        return back()->with('success', 'Utilizadores convidados: ' . implode(', ', $attached));
    }

    /**
     * Complete onboarding: store checklist and mark onboarding completed in tenant config
     */
    public function completeOnboarding(Request $request, Tenant $tenant)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'Administrator','Administrador']), 403);

        $data = $request->validate([
            'checklist' => ['nullable', 'array'],
            'initial_permissions' => ['nullable', 'array'],
            'initial_permissions.*' => ['string'],
        ]);

        $cfg = $tenant->config ?? [];
        $cfg['onboarding'] = [
            'completed' => true,
            'completed_at' => now()->toDateTimeString(),
            'checklist' => $data['checklist'] ?? [],
        ];

        if (!empty($data['initial_permissions'])) {
            $cfg['initial_permissions'] = array_values(array_filter($data['initial_permissions'], fn($v) => is_string($v) && $v !== ''));
        }

        $tenant->config = $cfg;
        $tenant->save();

        return redirect('/dashboard')->with('success', 'Onboarding concluído.');
    }
}