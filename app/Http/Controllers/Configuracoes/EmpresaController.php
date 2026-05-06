<?php

namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EmpresaController extends Controller
{
    public function show()
    {
        $empresa = Empresa::first() ?? new Empresa();
        return Inertia::render('Configuracoes/Empresa', ['empresa' => $empresa]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nome'          => ['required', 'string', 'max:255'],
            'morada'        => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'regex:/^\d{4}-\d{3}$/'],
            'localidade'    => ['nullable', 'string', 'max:255'],
            'nif'           => ['nullable', 'string', 'max:20'],
            'logotipo'      => ['nullable', 'image', 'max:2048'],
        ]);

        $empresa = Empresa::first() ?? new Empresa();

        if ($request->hasFile('logotipo')) {
            if ($empresa->logotipo) {
                Storage::disk('local')->delete($empresa->logotipo);
            }
            $validated['logotipo'] = $request->file('logotipo')
                ->store('empresa', 'local');
        }

        $empresa->fill($validated)->save();

        return back()->with('success', 'Dados da empresa atualizados.');
    }
}