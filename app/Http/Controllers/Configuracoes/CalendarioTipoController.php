<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\CalendarioTipo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioTipoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/CalendarioTipos', [
            'tipos' => CalendarioTipo::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cor'  => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        CalendarioTipo::create($request->only('nome', 'cor'));
        return back();
    }

    public function update(Request $request, CalendarioTipo $tipo)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cor'  => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'ativo' => ['boolean'],
        ]);
        $tipo->update($request->only('nome', 'cor', 'ativo'));
        return back();
    }

    public function destroy(CalendarioTipo $tipo)
    {
        $tipo->delete();
        return back();
    }
}