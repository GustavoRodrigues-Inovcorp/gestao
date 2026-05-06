<?php
namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\ContactoFuncao;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactoController extends Controller
{
    public function index()
    {
        return Inertia::render('Contactos/Index', [
            'contactos' => Contacto::with(['entidade:id,nome', 'funcao:id,nome'])
                ->orderBy('nome')
                ->orderBy('apelido')
                ->get(),
            'entidades' => Entidade::orderBy('nome')->get(['id', 'nome']),
            'funcoes' => ContactoFuncao::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'proximoNumero' => Contacto::proximoNumero(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id' => ['required', 'exists:entidades,id'],
            'nome' => ['required', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'funcao_id' => ['nullable', 'exists:contactos_funcoes,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'rgpd' => ['boolean'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['boolean'],
        ]);

        $validated['numero'] = Contacto::proximoNumero();
        Contacto::create($validated);

        return back()->with('success', 'Contacto criado com sucesso.');
    }

    public function update(Request $request, Contacto $contacto)
    {
        $validated = $request->validate([
            'entidade_id' => ['required', 'exists:entidades,id'],
            'nome' => ['required', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'funcao_id' => ['nullable', 'exists:contactos_funcoes,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'rgpd' => ['boolean'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['boolean'],
        ]);

        $contacto->update($validated);

        return back()->with('success', 'Contacto atualizado.');
    }

    public function destroy(Contacto $contacto)
    {
        $contacto->delete();
        return back();
    }
}
