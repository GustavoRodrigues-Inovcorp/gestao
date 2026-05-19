<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\Artigo;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class EncomendaFornecedorController extends Controller
{
    public function index()
    {
        return Inertia::render('Encomendas/Fornecedores', [
            'encomendas' => EncomendaFornecedor::with('fornecedor')->orderByDesc('id')->get()->map(fn($e) => [
                'id'            => $e->id,
                'numero'        => $e->numero,
                'data'          => $e->data?->format('d/m/Y'),
                'validade'     => $e->validade?->format('d/m/Y'),
                'validade_raw' => $e->validade?->format('Y-m-d'),
                'fornecedor'    => $e->fornecedor->nome,
                'fornecedor_id' => $e->fornecedor_id,
                'valor_total'   => $e->valor_total,
                'estado'        => $e->estado,
                'encomenda_id'  => $e->encomenda_id,
            ]),
            'fornecedores'  => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'       => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco']),
            'proximoNumero' => EncomendaFornecedor::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fornecedor_id'        => ['required', 'exists:entidades,id'],
            'validade'             => ['nullable', 'date'],
            'estado'               => ['required', 'in:rascunho,fechado'],
            'linhas'               => ['required', 'array', 'min:1'],
            'linhas.*.nome'        => ['required', 'string'],
            'linhas.*.quantidade'  => ['required', 'integer', 'min:1'],
            'linhas.*.preco_custo' => ['required', 'numeric', 'min:0'],
            'linhas.*.iva'         => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'   => ['nullable', 'exists:artigos,id'],
        ]);

        $encomenda = EncomendaFornecedor::create([
            'numero'        => EncomendaFornecedor::proximoNumero(),
            'data'          => $validated['estado'] === 'fechado' ? now() : null,
            'validade'      => $validated['validade'] ?? null,
            'fornecedor_id' => $validated['fornecedor_id'],
            'estado'        => $validated['estado'],
            'valor_total'   => 0,
        ]);

        foreach ($validated['linhas'] as $linha) {
            $encomenda->linhas()->create([
                'artigo_id'   => $linha['artigo_id'] ?? null,
                'referencia'  => $linha['referencia'] ?? null,
                'nome'        => $linha['nome'],
                'quantidade'  => $linha['quantidade'],
                'preco_custo' => $linha['preco_custo'],
                'iva'         => $linha['iva'] ?? 0,
                'subtotal'    => $linha['quantidade'] * $linha['preco_custo'],
            ]);
        }

        $encomenda->calcularTotal();
        LogHelper::log('Encomendas Fornecedor', "Criou encomenda fornecedor Nº {$encomenda->numero}");

        return back()->with('success', 'Encomenda de fornecedor criada.');
    }

    public function update(Request $request, EncomendaFornecedor $encomendaFornecedor)
    {
        $validated = $request->validate([
            'fornecedor_id'        => ['required', 'exists:entidades,id'],
            'validade'             => ['nullable', 'date'],
            'estado'               => ['required', 'in:rascunho,fechado'],
            'linhas'               => ['required', 'array', 'min:1'],
            'linhas.*.nome'        => ['required', 'string'],
            'linhas.*.quantidade'  => ['required', 'integer', 'min:1'],
            'linhas.*.preco_custo' => ['required', 'numeric', 'min:0'],
            'linhas.*.iva'         => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'   => ['nullable', 'exists:artigos,id'],
        ]);

        $encomendaFornecedor->update([
            'fornecedor_id' => $validated['fornecedor_id'],
            'validade'      => $validated['validade'] ?? null,
            'estado'        => $validated['estado'],
            'data'          => $validated['estado'] === 'fechado' && !$encomendaFornecedor->data ? now() : $encomendaFornecedor->data,
        ]);

        $encomendaFornecedor->linhas()->delete();

        foreach ($validated['linhas'] as $linha) {
            $encomendaFornecedor->linhas()->create([
                'artigo_id'   => $linha['artigo_id'] ?? null,
                'referencia'  => $linha['referencia'] ?? null,
                'nome'        => $linha['nome'],
                'quantidade'  => $linha['quantidade'],
                'preco_custo' => $linha['preco_custo'],
                'iva'         => $linha['iva'] ?? 0,
                'subtotal'    => $linha['quantidade'] * $linha['preco_custo'],
            ]);
        }

        $encomendaFornecedor->calcularTotal();
        LogHelper::log('Encomendas Fornecedor', "Atualizou encomenda fornecedor Nº {$encomendaFornecedor->numero}");

        return back()->with('success', 'Encomenda atualizada.');
    }

    public function destroy(EncomendaFornecedor $encomendaFornecedor)
    {
        LogHelper::log('Encomendas Fornecedor', "Eliminou encomenda fornecedor Nº {$encomendaFornecedor->numero}");
        $encomendaFornecedor->delete();
        return back();
    }

    public function pdf(EncomendaFornecedor $encomendaFornecedor)
    {
        $encomendaFornecedor->load(['fornecedor', 'linhas.artigo']);
        $empresa = Empresa::first();
        LogHelper::log('Encomendas Fornecedor', "Download PDF encomenda fornecedor Nº {$encomendaFornecedor->numero}");
        $pdf = Pdf::loadView('pdf.encomenda_fornecedor', ['encomenda' => $encomendaFornecedor, 'empresa' => $empresa])->setPaper('a4');
        return $pdf->download('encomenda-fornecedor-' . $encomendaFornecedor->numero . '.pdf');
    }

    public function linhas(EncomendaFornecedor $encomendaFornecedor)
    {
        return $encomendaFornecedor->linhas->map(fn($l) => [
            'artigo_id'   => $l->artigo_id,
            'referencia'  => $l->referencia,
            'nome'        => $l->nome,
            'quantidade'  => $l->quantidade,
            'preco_custo' => $l->preco_custo,
            'iva'         => $l->iva,
        ]);
    }
}