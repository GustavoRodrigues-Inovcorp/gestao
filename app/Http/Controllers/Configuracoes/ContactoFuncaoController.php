<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\ContactoFuncao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactoFuncaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/ContactosFuncoes', [
            'funcoes' => ContactoFuncao::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => ['required', 'string', 'max:255']]);
        ContactoFuncao::create($request->only('nome'));
        return back();
    }

    public function update(Request $request, ContactoFuncao $funcao)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['boolean'],
        ]);
        $funcao->update($request->only('nome', 'ativo'));
        return back();
    }

    public function destroy(ContactoFuncao $funcao)
    {
        $funcao->delete();
        return back();
    }
}