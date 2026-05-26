<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Entidade;
use App\Models\Pais;
use App\Services\SubscricaoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntidadeController extends Controller
{
    public function clientes()
    {
        return Inertia::render('Entidades/Clientes', [
            'entidades' => Entidade::with('pais')->where('is_cliente', true)->orderBy('nome')->get(),
            'paises' => Pais::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'proximoNumero' => Entidade::proximoNumero(),
        ]);
    }

    public function fornecedores()
    {
        return Inertia::render('Entidades/Fornecedores', [
            'entidades' => Entidade::with('pais')->where('is_fornecedor', true)->orderBy('nome')->get(),
            'paises' => Pais::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'proximoNumero' => Entidade::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'is_cliente'    => ['boolean'],
            'is_fornecedor' => ['boolean'],
            'nif'           => ['nullable', 'string', 'max:20'],
            'nome'          => ['required', 'string', 'max:255'],
            'morada'        => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'regex:/^\d{4}-\d{3}$/'],
            'localidade'    => ['nullable', 'string', 'max:255'],
            'pais_id'       => ['nullable', 'exists:paises,id'],
            'telefone'      => ['nullable', 'string', 'max:20'],
            'telemovel'     => ['nullable', 'string', 'max:20'],
            'website'       => ['nullable', 'url', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255'],
            'rgpd'          => ['boolean'],
            'observacoes'   => ['nullable', 'string'],
            'ativo'         => ['boolean'],
        ]);

        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        if ($tenant && !empty($validated['is_cliente'])) {
            app(SubscricaoService::class)->validarLimiteCriacao($tenant, 'clientes');
        }

        if (!empty($validated['nif'])) {
            if (Entidade::where('nif', $validated['nif'])->exists()) {
                return back()->withErrors(['nif' => 'Este NIF já existe.']);
            }
        }

        $validated['numero'] = Entidade::proximoNumero();
        $entidade = Entidade::create($validated);

        $tipo = $entidade->is_cliente && $entidade->is_fornecedor ? 'Cliente/Fornecedor' : ($entidade->is_cliente ? 'Cliente' : 'Fornecedor');
        LogHelper::log('Clientes / Fornecedores', "Criou {$tipo}: {$entidade->nome}");

        return back()->with('success', 'Entidade criada com sucesso.');
    }

    public function update(Request $request, Entidade $entidade)
    {
        $validated = $request->validate([
            'is_cliente'    => ['boolean'],
            'is_fornecedor' => ['boolean'],
            'nif'           => ['nullable', 'string', 'max:20'],
            'nome'          => ['required', 'string', 'max:255'],
            'morada'        => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'regex:/^\d{4}-\d{3}$/'],
            'localidade'    => ['nullable', 'string', 'max:255'],
            'pais_id'       => ['nullable', 'exists:paises,id'],
            'telefone'      => ['nullable', 'string', 'max:20'],
            'telemovel'     => ['nullable', 'string', 'max:20'],
            'website'       => ['nullable', 'url', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255'],
            'rgpd'          => ['boolean'],
            'observacoes'   => ['nullable', 'string'],
            'ativo'         => ['boolean'],
        ]);

        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        $novoEstadoCliente = array_key_exists('is_cliente', $validated)
            ? (bool) $validated['is_cliente']
            : $entidade->is_cliente;

        if ($tenant && !$entidade->is_cliente && $novoEstadoCliente) {
            app(SubscricaoService::class)->validarLimiteCriacao($tenant, 'clientes');
        }

        if (!empty($validated['nif'])) {
            if (Entidade::where('nif', $validated['nif'])->where('id', '!=', $entidade->id)->exists()) {
                return back()->withErrors(['nif' => 'Este NIF já existe.']);
            }
        }

        $entidade->update($validated);
        LogHelper::log('Clientes / Fornecedores', "Atualizou entidade: {$entidade->nome}");

        return back()->with('success', 'Entidade atualizada.');
    }

    public function destroy(Entidade $entidade)
    {
        LogHelper::log('Clientes / Fornecedores', "Eliminou entidade: {$entidade->nome}");
        $entidade->delete();
        return back();
    }

    public function checkNif(Request $request)
    {
        $exists = Entidade::where('nif', $request->nif)
            ->when($request->id, fn($q) => $q->where('id', '!=', $request->id))
            ->exists();
        return response()->json(['exists' => $exists]);
    }
}