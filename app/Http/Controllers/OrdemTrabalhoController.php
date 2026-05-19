<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\OrdemTrabalho;
use App\Models\OrdemTrabalhoLinha;
use App\Models\Entidade;
use App\Models\Contacto;
use App\Models\Artigo;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrdemTrabalhoController extends Controller
{
    public function index()
    {
        return Inertia::render('OrdensTrabalho/Index', [
            'ordens' => OrdemTrabalho::with(['entidade', 'contacto', 'user'])
                ->orderByDesc('id')
                ->get()
                ->map(fn($o) => [
                    'id'             => $o->id,
                    'numero'         => $o->numero,
                    'data'           => $o->data->format('d/m/Y'),
                    'data_raw'       => $o->data->format('Y-m-d'),
                    'entidade'       => $o->entidade->nome,
                    'entidade_id'    => $o->entidade_id,
                    'contacto'       => $o->contacto ? $o->contacto->nome . ' ' . ($o->contacto->apelido ?? '') : null,
                    'contacto_id'    => $o->contacto_id,
                    'descricao'      => $o->descricao,
                    'observacoes'    => $o->observacoes,
                    'user'           => $o->user->name,
                    'user_id'        => $o->user_id,
                    'estado'         => $o->estado,
                    'data_prevista'  => $o->data_prevista?->format('d/m/Y'),
                    'data_prevista_raw' => $o->data_prevista?->format('Y-m-d'),
                    'data_conclusao' => $o->data_conclusao?->format('d/m/Y'),
                    'data_conclusao_raw' => $o->data_conclusao?->format('Y-m-d'),
                    'total'          => $o->calcularTotal(),
                ]),
            'clientes'      => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'contactos'     => Contacto::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'apelido', 'entidade_id']),
            'artigos'       => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco']),
            'utilizadores'  => User::where('active', true)->orderBy('name')->get(['id', 'name']),
            'proximoNumero' => OrdemTrabalho::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id'    => ['required', 'exists:entidades,id'],
            'contacto_id'    => ['nullable', 'exists:contactos,id'],
            'data'           => ['required', 'date'],
            'descricao'      => ['nullable', 'string'],
            'observacoes'    => ['nullable', 'string'],
            'user_id'        => ['nullable', 'exists:users,id'],
            'estado'         => ['required', 'in:aberta,em_progresso,concluida,cancelada'],
            'data_prevista'  => ['nullable', 'date'],
            'data_conclusao' => ['nullable', 'date'],
            'linhas'         => ['nullable', 'array'],
            'linhas.*.descricao'  => ['required', 'string'],
            'linhas.*.quantidade' => ['required', 'numeric', 'min:0'],
            'linhas.*.preco'      => ['required', 'numeric', 'min:0'],
            'linhas.*.unidade'    => ['nullable', 'string'],
            'linhas.*.artigo_id'  => ['nullable', 'exists:artigos,id'],
        ]);

        $ordem = OrdemTrabalho::create([
            'numero'         => OrdemTrabalho::proximoNumero(),
            'data'           => $validated['data'],
            'entidade_id'    => $validated['entidade_id'],
            'contacto_id'    => $validated['contacto_id'] ?? null,
            'descricao'      => $validated['descricao'] ?? null,
            'observacoes'    => $validated['observacoes'] ?? null,
            'user_id'        => $validated['user_id'] ?? auth()->id(),
            'estado'         => $validated['estado'],
            'data_prevista'  => $validated['data_prevista'] ?? null,
            'data_conclusao' => $validated['data_conclusao'] ?? null,
        ]);

        foreach ($validated['linhas'] ?? [] as $linha) {
            $subtotal = $linha['quantidade'] * $linha['preco'];
            $ordem->linhas()->create([
                'artigo_id'  => $linha['artigo_id'] ?? null,
                'descricao'  => $linha['descricao'],
                'quantidade' => $linha['quantidade'],
                'unidade'    => $linha['unidade'] ?? null,
                'preco'      => $linha['preco'],
                'subtotal'   => $subtotal,
            ]);
        }

        LogHelper::log('Ordens de Trabalho', "Criou ordem Nº {$ordem->numero}");
        return back()->with('success', 'Ordem de trabalho criada.');
    }

    public function update(Request $request, OrdemTrabalho $ordemTrabalho)
    {
        $validated = $request->validate([
            'entidade_id'    => ['required', 'exists:entidades,id'],
            'contacto_id'    => ['nullable', 'exists:contactos,id'],
            'data'           => ['required', 'date'],
            'descricao'      => ['nullable', 'string'],
            'observacoes'    => ['nullable', 'string'],
            'user_id'        => ['nullable', 'exists:users,id'],
            'estado'         => ['required', 'in:aberta,em_progresso,concluida,cancelada'],
            'data_prevista'  => ['nullable', 'date'],
            'data_conclusao' => ['nullable', 'date'],
            'linhas'         => ['nullable', 'array'],
            'linhas.*.descricao'  => ['required', 'string'],
            'linhas.*.quantidade' => ['required', 'numeric', 'min:0'],
            'linhas.*.preco'      => ['required', 'numeric', 'min:0'],
            'linhas.*.unidade'    => ['nullable', 'string'],
            'linhas.*.artigo_id'  => ['nullable', 'exists:artigos,id'],
        ]);

        $ordemTrabalho->update([
            'data'           => $validated['data'],
            'entidade_id'    => $validated['entidade_id'],
            'contacto_id'    => isset($validated['contacto_id']) && $validated['contacto_id'] !== 'sem-contacto'
                                ? $validated['contacto_id'] : null,
            'descricao'      => $validated['descricao'] ?? null,
            'observacoes'    => $validated['observacoes'] ?? null,
            'user_id'        => $validated['user_id'] ?? $ordemTrabalho->user_id,
            'estado'         => $validated['estado'],
            'data_prevista'  => !empty($validated['data_prevista']) ? $validated['data_prevista'] : null,
            'data_conclusao' => !empty($validated['data_conclusao']) ? $validated['data_conclusao'] : null,
        ]);

        // Apaga linhas antigas
        OrdemTrabalhoLinha::where('ordem_trabalho_id', $ordemTrabalho->id)->delete();

        // Cria linhas novas com ID explícito
        foreach ($validated['linhas'] ?? [] as $linha) {
            OrdemTrabalhoLinha::insert([
                'ordem_trabalho_id' => $ordemTrabalho->id,
                'artigo_id'         => $linha['artigo_id'] ?? null,
                'descricao'         => $linha['descricao'],
                'quantidade'        => $linha['quantidade'],
                'unidade'           => $linha['unidade'] ?? null,
                'preco'             => $linha['preco'],
                'subtotal'          => $linha['quantidade'] * $linha['preco'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        LogHelper::log('Ordens de Trabalho', "Atualizou ordem Nº {$ordemTrabalho->numero}");
        return back()->with('success', 'Ordem de trabalho atualizada.');
    }

    public function destroy(OrdemTrabalho $ordemTrabalho)
    {
        LogHelper::log('Ordens de Trabalho', "Eliminou ordem Nº {$ordemTrabalho->numero}");
        $ordemTrabalho->delete();
        return back();
    }

    public function linhas(OrdemTrabalho $ordemTrabalho)
    {
        return $ordemTrabalho->linhas->map(fn($l) => [
            'artigo_id'  => $l->artigo_id,
            'descricao'  => $l->descricao,
            'quantidade' => $l->quantidade,
            'unidade'    => $l->unidade,
            'preco'      => $l->preco,
        ]);
    }
}