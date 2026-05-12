<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Proposta;
use App\Models\Entidade;
use App\Models\Artigo;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PropostaController extends Controller
{
    public function index()
    {
        return Inertia::render('Propostas/Index', [
            'propostas' => Proposta::with('entidade')->orderByDesc('id')->get()->map(fn($p) => [
                'id'           => $p->id,
                'numero'       => $p->numero,
                'data'         => $p->data?->format('d/m/Y'),
                'validade'     => $p->validade?->format('d/m/Y'),
                'validade_raw' => $p->validade?->format('Y-m-d'),
                'entidade_id'  => $p->entidade_id,
                'cliente'      => $p->entidade->nome,
                'valor_total'  => $p->valor_total,
                'estado'       => $p->estado,
            ]),
            'clientes'       => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'        => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco', 'iva_id']),
            'fornecedores'   => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'proximoNumero'  => Proposta::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id'            => ['required', 'exists:entidades,id'],
            'validade'               => ['nullable', 'date'],
            'estado'                 => ['required', 'in:rascunho,fechado'],
            'linhas'                 => ['required', 'array', 'min:1'],
            'linhas.*.nome'          => ['required', 'string'],
            'linhas.*.quantidade'    => ['required', 'integer', 'min:1'],
            'linhas.*.preco_venda'   => ['required', 'numeric', 'min:0'],
            'linhas.*.preco_custo'   => ['nullable', 'numeric', 'min:0'],
            'linhas.*.iva'           => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'     => ['nullable', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
        ]);

        $proposta = Proposta::create([
            'numero'      => Proposta::proximoNumero(),
            'data'        => $validated['estado'] === 'fechado' ? now() : null,
            'entidade_id' => $validated['entidade_id'],
            'validade'    => $validated['validade'] ?? now()->addDays(30),
            'estado'      => $validated['estado'],
            'valor_total' => 0,
        ]);

        foreach ($validated['linhas'] as $linha) {
            $proposta->linhas()->create([
                'artigo_id'     => $linha['artigo_id'] ?? null,
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'referencia'    => $linha['referencia'] ?? null,
                'nome'          => $linha['nome'],
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'] ?? 0,
                'subtotal'      => $linha['quantidade'] * $linha['preco_venda'],
            ]);
        }

        $proposta->calcularTotal();
        LogHelper::log('Propostas', "Criou proposta Nº {$proposta->numero}");

        return back()->with('success', 'Proposta criada.');
    }

    public function update(Request $request, Proposta $proposta)
    {
        $validated = $request->validate([
            'entidade_id'            => ['required', 'exists:entidades,id'],
            'validade'               => ['nullable', 'date'],
            'estado'                 => ['required', 'in:rascunho,fechado'],
            'linhas'                 => ['required', 'array', 'min:1'],
            'linhas.*.nome'          => ['required', 'string'],
            'linhas.*.quantidade'    => ['required', 'integer', 'min:1'],
            'linhas.*.preco_venda'   => ['required', 'numeric', 'min:0'],
            'linhas.*.preco_custo'   => ['nullable', 'numeric', 'min:0'],
            'linhas.*.iva'           => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'     => ['nullable', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
        ]);

        $proposta->update([
            'entidade_id' => $validated['entidade_id'],
            'validade'    => $validated['validade'],
            'estado'      => $validated['estado'],
            'data'        => $validated['estado'] === 'fechado' && !$proposta->data ? now() : $proposta->data,
        ]);

        $proposta->linhas()->delete();

        foreach ($validated['linhas'] as $linha) {
            $proposta->linhas()->create([
                'artigo_id'     => $linha['artigo_id'] ?? null,
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'referencia'    => $linha['referencia'] ?? null,
                'nome'          => $linha['nome'],
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'] ?? 0,
                'subtotal'      => $linha['quantidade'] * $linha['preco_venda'],
            ]);
        }

        $proposta->calcularTotal();
        LogHelper::log('Propostas', "Atualizou proposta Nº {$proposta->numero}");

        return back()->with('success', 'Proposta atualizada.');
    }

    public function destroy(Proposta $proposta)
    {
        LogHelper::log('Propostas', "Eliminou proposta Nº {$proposta->numero}");
        $proposta->delete();
        return back();
    }

    public function pdf(Proposta $proposta)
    {
        $proposta->load(['entidade', 'linhas.artigo']);
        $empresa = Empresa::first();
        LogHelper::log('Propostas', "Download PDF proposta Nº {$proposta->numero}");
        $pdf = Pdf::loadView('pdf.proposta', ['proposta' => $proposta, 'empresa' => $empresa])->setPaper('a4');
        return $pdf->download('proposta-' . $proposta->numero . '.pdf');
    }

    public function converter(Proposta $proposta)
    {
        abort_unless($proposta->estado === 'fechado', 422, 'Só é possível converter propostas fechadas.');

        $encomenda = \App\Models\Encomenda::create([
            'numero'      => \App\Models\Encomenda::proximoNumero(),
            'entidade_id' => $proposta->entidade_id,
            'validade'    => $proposta->validade,
            'estado'      => 'rascunho',
            'valor_total' => $proposta->valor_total,
            'proposta_id' => $proposta->id,
        ]);

        foreach ($proposta->linhas as $linha) {
            $encomenda->linhas()->create($linha->only([
                'artigo_id', 'fornecedor_id', 'referencia',
                'nome', 'quantidade', 'preco_venda',
                'preco_custo', 'iva', 'subtotal',
            ]));
        }

        LogHelper::log('Propostas', "Converteu proposta Nº {$proposta->numero} em encomenda Nº {$encomenda->numero}");

        return back()->with('success', 'Proposta convertida em encomenda.');
    }
}