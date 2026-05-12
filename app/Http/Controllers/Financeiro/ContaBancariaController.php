<?php
namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\ContaBancaria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContaBancariaController extends Controller
{
    public function index()
    {
        return Inertia::render('Financeiro/ContasBancarias', [
            'contas' => ContaBancaria::orderBy('banco')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'banco'   => ['required', 'string', 'max:255'],
            'iban'    => ['required', 'string', 'max:34', 'unique:contas_bancarias,iban'],
            'swift'   => ['nullable', 'string', 'max:11'],
            'titular' => ['required', 'string', 'max:255'],
            'saldo'   => ['nullable', 'numeric'],
            'ativo'   => ['boolean'],
        ]);

        ContaBancaria::create($request->only('banco', 'iban', 'swift', 'titular', 'saldo', 'ativo'));
        return back()->with('success', 'Conta criada.');
    }

    public function update(Request $request, ContaBancaria $contaBancaria)
    {
        $request->validate([
            'banco'   => ['required', 'string', 'max:255'],
            'iban'    => ['required', 'string', 'max:34', 'unique:contas_bancarias,iban,' . $contaBancaria->id],
            'swift'   => ['nullable', 'string', 'max:11'],
            'titular' => ['required', 'string', 'max:255'],
            'saldo'   => ['nullable', 'numeric'],
            'ativo'   => ['boolean'],
        ]);

        $contaBancaria->update($request->only('banco', 'iban', 'swift', 'titular', 'saldo', 'ativo'));
        return back()->with('success', 'Conta atualizada.');
    }

    public function destroy(ContaBancaria $contaBancaria)
    {
        $contaBancaria->delete();
        return back();
    }
}