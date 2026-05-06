<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\CalendarioAcao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioAcaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/CalendarioAcoes', [
            'acoes' => CalendarioAcao::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => ['required', 'string', 'max:255']]);
        CalendarioAcao::create($request->only('nome'));
        return back();
    }

    public function update(Request $request, CalendarioAcao $acao)
    {
        $request->validate([
            'nome'  => ['required', 'string', 'max:255'],
            'ativo' => ['boolean'],
        ]);
        $acao->update($request->only('nome', 'ativo'));
        return back();
    }

    public function destroy(CalendarioAcao $acao)
    {
        $acao->delete();
        return back();
    }
}