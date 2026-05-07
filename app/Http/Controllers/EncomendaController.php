<?php
namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\Artigo;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class EncomendaController extends Controller
{
    public function index()
    {
        // Lista de encomendas e dados auxiliares para os modais de criação/edição.
        return Inertia::render('Encomendas/Clientes', [
            'encomendas' => Encomenda::with('entidade')
                ->orderByDesc('id')
                ->get()
                ->map(fn($e) => [
                    'id'          => $e->id,
                    'numero'      => $e->numero,
                    'data'        => $e->data?->format('d/m/Y'),
                    'validade'    => $e->validade?->format('d/m/Y'),
                    'validade_raw' => $e->validade?->format('Y-m-d'),
                    'cliente'     => $e->entidade->nome,
                    'entidade_id' => $e->entidade_id,
                    'valor_total' => $e->valor_total,
                    'estado'      => $e->estado,
                    'proposta_id' => $e->proposta_id,
                ]),
            'clientes' => Entidade::query()->where('is_cliente', '=', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome']),
            'artigos' => Artigo::query()->where('ativo', '=', true)
                ->orderBy('nome')
                ->get(['id', 'referencia', 'nome', 'preco', 'iva_id']),
            'fornecedores' => Entidade::query()->where('is_fornecedor', '=', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome']),
            'proximoNumero' => Encomenda::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        // Cria a encomenda e todas as linhas em cascata.
        $validated = $request->validate([
            'entidade_id' => ['required', 'exists:entidades,id'],
            'validade'    => ['nullable', 'date'],
            'estado'      => ['required', 'in:rascunho,fechado'],
            'linhas'      => ['required', 'array', 'min:1'],
            'linhas.*.nome'          => ['required', 'string'],
            'linhas.*.quantidade'    => ['required', 'integer', 'min:1'],
            'linhas.*.preco_venda'   => ['required', 'numeric', 'min:0'],
            'linhas.*.preco_custo'   => ['nullable', 'numeric', 'min:0'],
            'linhas.*.iva'           => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'     => ['nullable', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
        ]);

        $encomenda = Encomenda::create([
            'numero'      => Encomenda::proximoNumero(),
            'data'        => $validated['estado'] === 'fechado' ? now() : null,
            'entidade_id' => $validated['entidade_id'],
            'validade'    => $validated['validade'] ?? null,
            'estado'      => $validated['estado'],
            'valor_total' => 0,
        ]);

        foreach ($validated['linhas'] as $linha) {
            $subtotal = $linha['quantidade'] * $linha['preco_venda'];
            $encomenda->linhas()->create([
                'artigo_id'     => $linha['artigo_id'] ?? null,
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'referencia'    => $linha['referencia'] ?? null,
                'nome'          => $linha['nome'],
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'] ?? 0,
                'subtotal'      => $subtotal,
            ]);
        }

        $encomenda->calcularTotal();
        return back()->with('success', 'Encomenda criada.');
    }

    public function update(Request $request, Encomenda $encomenda)
    {
        // Atualiza o documento substituindo as linhas para manter o cálculo simples.
        $validated = $request->validate([
            'entidade_id' => ['required', 'exists:entidades,id'],
            'validade'    => ['nullable', 'date'],
            'estado'      => ['required', 'in:rascunho,fechado'],
            'linhas'      => ['required', 'array', 'min:1'],
            'linhas.*.nome'          => ['required', 'string'],
            'linhas.*.quantidade'    => ['required', 'integer', 'min:1'],
            'linhas.*.preco_venda'   => ['required', 'numeric', 'min:0'],
            'linhas.*.preco_custo'   => ['nullable', 'numeric', 'min:0'],
            'linhas.*.iva'           => ['nullable', 'numeric', 'min:0'],
            'linhas.*.artigo_id'     => ['nullable', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
        ]);

        $encomenda->update([
            'entidade_id' => $validated['entidade_id'],
            'validade'    => $validated['validade'],
            'estado'      => $validated['estado'],
            'data'        => $validated['estado'] === 'fechado' && !$encomenda->data ? now() : $encomenda->data,
        ]);

        $encomenda->linhas()->delete();

        foreach ($validated['linhas'] as $linha) {
            $subtotal = $linha['quantidade'] * $linha['preco_venda'];
            $encomenda->linhas()->create([
                'artigo_id'     => $linha['artigo_id'] ?? null,
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'referencia'    => $linha['referencia'] ?? null,
                'nome'          => $linha['nome'],
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'] ?? 0,
                'subtotal'      => $subtotal,
            ]);
        }

        $encomenda->calcularTotal();
        return back()->with('success', 'Encomenda atualizada.');
    }

    public function destroy(Encomenda $encomenda)
    {
        $encomenda->delete();
        return back();
    }

    public function pdf(Encomenda $encomenda)
    {
        // Renderização do PDF da encomenda com os relacionamentos já carregados.
        $encomenda->load(['entidade', 'linhas.artigo']);
        $empresa = Empresa::first();

        $pdf = Pdf::loadView('pdf.encomenda', [
            'encomenda' => $encomenda,
            'empresa'   => $empresa,
        ])->setPaper('a4');

        return $pdf->download('encomenda-' . $encomenda->numero . '.pdf');
    }

    public function converterParaFornecedor(Encomenda $encomenda)
    {
        abort_unless($encomenda->estado === 'fechado', 422, 'Só é possível converter encomendas fechadas.');

        $encomenda->load('linhas');

        // Agrupa as linhas pelo fornecedor para gerar documentos separados.
        $porFornecedor = $encomenda->linhas
            ->filter(fn($l) => $l->fornecedor_id)
            ->groupBy('fornecedor_id');

        foreach ($porFornecedor as $fornecedorId => $linhas) {
            $encForn = EncomendaFornecedor::create([
                'numero'       => EncomendaFornecedor::proximoNumero(),
                'fornecedor_id' => $fornecedorId,
                'encomenda_id' => $encomenda->id,
                'estado'       => 'rascunho',
                'valor_total'  => 0,
            ]);

            foreach ($linhas as $linha) {
                $subtotal = $linha->quantidade * $linha->preco_custo;
                $encForn->linhas()->create([
                    'artigo_id'   => $linha->artigo_id,
                    'referencia'  => $linha->referencia,
                    'nome'        => $linha->nome,
                    'quantidade'  => $linha->quantidade,
                    'preco_custo' => $linha->preco_custo,
                    'iva'         => $linha->iva,
                    'subtotal'    => $subtotal,
                ]);
            }

            $encForn->calcularTotal();
        }

        return back()->with('success', 'Encomendas de fornecedor criadas.');
    }

    public function linhas(Encomenda $encomenda)
    {
        return $encomenda->linhas->map(fn($l) => [
            'artigo_id'     => $l->artigo_id,
            'fornecedor_id' => $l->fornecedor_id,
            'referencia'    => $l->referencia,
            'nome'          => $l->nome,
            'quantidade'    => $l->quantidade,
            'preco_venda'   => $l->preco_venda,
            'preco_custo'   => $l->preco_custo,
            'iva'           => $l->iva,
        ]);
    }
}