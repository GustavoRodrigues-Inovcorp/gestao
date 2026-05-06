<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaisController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/Paises', [
            'paises' => Pais::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'   => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:3', 'unique:paises,codigo'],
        ]);
        Pais::create($request->only('nome', 'codigo'));
        return back();
    }

    public function update(Request $request, Pais $pais)
    {
        $request->validate([
            'nome'   => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:3', 'unique:paises,codigo,' . $pais->id],
            'ativo'  => ['boolean'],
        ]);
        $pais->update($request->only('nome', 'codigo', 'ativo'));
        return back();
    }

    public function destroy(Pais $pais)
    {
        $pais->delete();
        return back();
    }
}