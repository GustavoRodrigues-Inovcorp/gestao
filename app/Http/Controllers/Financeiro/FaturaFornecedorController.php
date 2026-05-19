<?php
namespace App\Http\Controllers\Financeiro;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Mail\ComprovativoPagamentoMail;
use App\Models\Entidade;
use App\Models\EncomendaFornecedor;
use App\Models\FaturaFornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FaturaFornecedorController extends Controller
{
    public function index()
    {
        return Inertia::render('Financeiro/FaturasFornecedor', [
            'faturas' => FaturaFornecedor::with(['fornecedor', 'encomendaFornecedor'])
                ->orderByDesc('id')
                ->get()
                ->map(fn($f) => [
                    'id'                      => $f->id,
                    'numero'                  => $f->numero,
                    'data_fatura'             => $f->data_fatura?->format('d/m/Y'),
                    'data_fatura_raw'         => $f->data_fatura?->format('Y-m-d'),      // ← adiciona
                    'data_vencimento'         => $f->data_vencimento?->format('d/m/Y'),
                    'data_vencimento_raw'     => $f->data_vencimento?->format('Y-m-d'),  // ← adiciona
                    'fornecedor'              => $f->fornecedor->nome,
                    'fornecedor_id'           => $f->fornecedor_id,
                    'encomenda_fornecedor_id' => $f->encomenda_fornecedor_id,
                    'encomenda_numero'        => $f->encomendaFornecedor?->numero,
                    'valor_total'             => $f->valor_total,
                    'documento'               => $f->documento ? '/financeiro/documento/' . $f->id : null,
                    'comprovativo'            => $f->comprovativo ? '/financeiro/comprovativo/' . $f->id : null,
                    'estado'                  => $f->estado,
                ]),
            'fornecedores' => Entidade::where('is_fornecedor', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome', 'email']),
            'encomendas' => EncomendaFornecedor::with('fornecedor')
                ->orderByDesc('id')
                ->get()
                ->map(fn($e) => [
                    'id'           => $e->id,
                    'numero'       => $e->numero,
                    'fornecedor_id' => $e->fornecedor_id,
                    'valor_total'  => $e->valor_total,
                ]),
            'proximoNumero' => FaturaFornecedor::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_fatura'             => ['required', 'date'],
            'data_vencimento'         => ['nullable', 'date'],
            'fornecedor_id'           => ['required', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'exists:encomendas_fornecedor,id'],
            'valor_total'             => ['required', 'numeric', 'min:0'],
            'documento'               => ['nullable', 'file', 'max:10240'],
            'comprovativo'            => ['nullable', 'file', 'max:10240'],
            'estado'                  => ['required', 'in:pendente,paga'],
        ]);

        if ($request->hasFile('documento')) {
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'local');
        }

        if ($request->hasFile('comprovativo')) {
            $validated['comprovativo'] = $request->file('comprovativo')
                ->store('faturas/comprovativos', 'local');
        }

        $validated['numero'] = FaturaFornecedor::proximoNumero();
        FaturaFornecedor::create($validated);

        return back()->with('success', 'Fatura criada.');
    }

    public function update(Request $request, FaturaFornecedor $faturaFornecedor)
    {
        $validated = $request->validate([
            'data_fatura'             => ['required', 'date'],
            'data_vencimento'         => ['nullable', 'date'],
            'fornecedor_id'           => ['required', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'exists:encomendas_fornecedor,id'],
            'valor_total'             => ['required', 'numeric', 'min:0'],
            'documento'               => ['nullable', 'file', 'max:10240'],
            'estado'                  => ['required', 'in:pendente,paga'],
        ]);

        if ($request->hasFile('documento')) {
            if ($faturaFornecedor->documento) {
                Storage::disk('local')->delete($faturaFornecedor->documento);
            }
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'local');
        }

        $faturaFornecedor->update($validated);
        
        LogHelper::log('Faturas Fornecedor', "Atualizou fatura Nº {$faturaFornecedor->numero}");
        return back()->with('success', 'Fatura atualizada.');
    }

    public function destroy(FaturaFornecedor $faturaFornecedor)
    {
        LogHelper::log('Faturas Fornecedor', "Eliminou fatura Nº {$faturaFornecedor->numero}");

        if ($faturaFornecedor->documento) {
            Storage::disk('local')->delete($faturaFornecedor->documento);
        }
        if ($faturaFornecedor->comprovativo) {
            Storage::disk('local')->delete($faturaFornecedor->comprovativo);
        }
        $faturaFornecedor->delete();
        return back();
    }

    public function enviarComprovativo(Request $request, FaturaFornecedor $faturaFornecedor)
    {
        $request->validate([
            'comprovativo' => ['required', 'file', 'max:10240'],
        ]);

        if ($faturaFornecedor->comprovativo) {
            Storage::disk('local')->delete($faturaFornecedor->comprovativo);
        }

        $path = $request->file('comprovativo')
            ->store('faturas/comprovativos', 'local');

        $faturaFornecedor->update(['comprovativo' => $path]);
        $faturaFornecedor->load('fornecedor');

        if ($faturaFornecedor->fornecedor->email) {
            try {
                Mail::to($faturaFornecedor->fornecedor->email)
                    ->send(new ComprovativoPagamentoMail($faturaFornecedor, $path));
            } catch (\Throwable $e) {
                report($e);

                LogHelper::log(
                    'Faturas Fornecedor',
                    "Falhou envio de comprovativo da fatura Nº {$faturaFornecedor->numero}: " . $e->getMessage()
                );

                return back()->with('error', 'O comprovativo foi guardado, mas não foi possível enviar o email.');
            }
        }

        LogHelper::log('Faturas Fornecedor', "Enviou comprovativo fatura Nº {$faturaFornecedor->numero}");

        return back()->with('success', 'Comprovativo enviado com sucesso.');
    }

    public function documento(FaturaFornecedor $faturaFornecedor)
    {
        abort_unless(!empty($faturaFornecedor->documento), 404);
        return response()->file(storage_path('app/private/' . $faturaFornecedor->documento));
    }

    public function comprovativo(FaturaFornecedor $faturaFornecedor)
    {
        abort_unless(!empty($faturaFornecedor->comprovativo), 404);
        return response()->file(storage_path('app/private/' . $faturaFornecedor->comprovativo));
    }
}