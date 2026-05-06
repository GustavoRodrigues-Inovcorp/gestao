<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\Iva;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IvaController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/Iva', [
            'taxas' => Iva::orderBy('percentagem')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'        => ['required', 'string', 'max:255'],
            'percentagem' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        Iva::create($request->only('nome', 'percentagem'));
        return back();
    }

    public function update(Request $request, Iva $iva)
    {
        $request->validate([
            'nome'        => ['required', 'string', 'max:255'],
            'percentagem' => ['required', 'numeric', 'min:0', 'max:100'],
            'ativo'       => ['boolean'],
        ]);
        $iva->update($request->only('nome', 'percentagem', 'ativo'));
        return back();
    }

    public function destroy(Iva $iva)
    {
        $iva->delete();
        return back();
    }
}