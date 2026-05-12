<?php
namespace App\Http\Controllers\Financeiro;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Mail\ComprovatiovPagamentoMail;
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
                    'id'                    => $f->id,
                    'numero'                => $f->numero,
                    'data_fatura'           => $f->data_fatura?->format('d/m/Y'),
                    'data_vencimento'       => $f->data_vencimento?->format('d/m/Y'),
                    'fornecedor'            => $f->fornecedor->nome,
                    'fornecedor_id'         => $f->fornecedor_id,
                    'encomenda_fornecedor_id' => $f->encomenda_fornecedor_id,
                    'encomenda_numero'      => $f->encomendaFornecedor?->numero,
                    'valor_total'           => $f->valor_total,
                    'documento'             => $f->documento ? '/financeiro/documento/' . $f->id : null,
                    'comprovativo'          => $f->comprovativo ? '/financeiro/comprovativo/' . $f->id : null,
                    'estado'                => $f->estado,
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
            'estado'                  => ['required', 'in:pendente,paga'],
        ]);

        if ($request->hasFile('documento')) {
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'private');
        }

        $validated['numero'] = FaturaFornecedor::proximoNumero();
        $fatura = FaturaFornecedor::create($validated);

        LogHelper::log('Faturas Fornecedor', "Criou fatura Nº {$fatura->numero}");
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
                Storage::disk('private')->delete($faturaFornecedor->documento);
            }
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'private');
        }

        $faturaFornecedor->update($validated);
        
        LogHelper::log('Faturas Fornecedor', "Atualizou fatura Nº {$faturaFornecedor->numero}");
        return back()->with('success', 'Fatura atualizada.');
    }

    public function destroy(FaturaFornecedor $faturaFornecedor)
    {
        LogHelper::log('Faturas Fornecedor', "Eliminou fatura Nº {$faturaFornecedor->numero}");

        if ($faturaFornecedor->documento) {
            Storage::disk('private')->delete($faturaFornecedor->documento);
        }
        if ($faturaFornecedor->comprovativo) {
            Storage::disk('private')->delete($faturaFornecedor->comprovativo);
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
            Storage::disk('private')->delete($faturaFornecedor->comprovativo);
        }

        $path = $request->file('comprovativo')
            ->store('faturas/comprovativos', 'private');

        $faturaFornecedor->update(['comprovativo' => $path]);

        $faturaFornecedor->load('fornecedor');

        if ($faturaFornecedor->fornecedor->email) {
            Mail::to($faturaFornecedor->fornecedor->email)
                ->send(new ComprovatiovPagamentoMail($faturaFornecedor, $path));
        }

        activity('faturas_fornecedor')
            ->causedBy(auth()->user())
            ->performedOn($faturaFornecedor)
            ->log('Enviou comprovativo de pagamento');

        LogHelper::log('Faturas Fornecedor', "Enviou comprovativo fatura Nº {$faturaFornecedor->numero}");
        return back()->with('success', 'Comprovativo enviado.');
    }

    public function documento(FaturaFornecedor $faturaFornecedor)
    {
        abort_unless($faturaFornecedor->documento, 404);
        return response()->file(storage_path('app/private/' . $faturaFornecedor->documento));
    }

    public function comprovativo(FaturaFornecedor $faturaFornecedor)
    {
        abort_unless($faturaFornecedor->comprovativo, 404);
        return response()->file(storage_path('app/private/' . $faturaFornecedor->comprovativo));
    }
}