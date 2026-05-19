<?php
namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\ContaCorrenteCliente;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContaCorrenteClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Financeiro/ContaCorrenteClientes', [
            'movimentos' => ContaCorrenteCliente::with(['entidade', 'user'])
                ->orderByDesc('data')
                ->orderByDesc('id')
                ->get()
                ->map(fn($m) => [
                    'id'         => $m->id,
                    'data'       => $m->data->format('d/m/Y'),
                    'data_raw'   => $m->data->format('Y-m-d'),
                    'entidade'   => $m->entidade->nome,
                    'entidade_id' => $m->entidade_id,
                    'descricao'  => $m->descricao,
                    'debito'     => $m->debito,
                    'credito'    => $m->credito,
                    'saldo'      => $m->saldo,
                    'tipo'       => $m->tipo,
                    'referencia' => $m->referencia,
                    'user'       => $m->user->name,
                ]),
            'clientes' => Entidade::where('is_cliente', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id'   => ['required', 'exists:entidades,id'],
            'data_movimento' => ['required', 'date'],
            'descricao'     => ['required', 'string', 'max:255'],
            'debito'        => ['nullable', 'numeric', 'min:0'],
            'credito'       => ['nullable', 'numeric', 'min:0'],
            'tipo'          => ['required', 'in:fatura,pagamento,nota_credito,outro'],
            'referencia'    => ['nullable', 'string', 'max:100'],
        ]);

        // Calcula saldo acumulado do cliente
        $ultimoMovimento = ContaCorrenteCliente::where('entidade_id', $validated['entidade_id'])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->first();

        $saldoAnterior = $ultimoMovimento?->saldo ?? 0;
        $debito  = $validated['debito'] ?? 0;
        $credito = $validated['credito'] ?? 0;
        $saldo   = $saldoAnterior + $debito - $credito;

        ContaCorrenteCliente::create([
            ...$validated,
            'entidade_id' => $validated['entidade_id'],
            'data'        => $validated['data_movimento'],
            'debito'  => $debito,
            'credito' => $credito,
            'saldo'   => $saldo,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Movimento registado.');
    }

    public function destroy(ContaCorrenteCliente $contaCorrenteCliente)
    {
        $contaCorrenteCliente->delete();
        return back();
    }
}