<?php
namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\Artigo;
use App\Models\Iva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArtigoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/Artigos', [
            'artigos' => Artigo::with('iva')->orderBy('nome')->get()->map(fn($a) => [
                'id'         => $a->id,
                'referencia' => $a->referencia,
                'nome'       => $a->nome,
                'descricao'  => $a->descricao,
                'preco'      => $a->preco,
                'iva'        => $a->iva?->nome,
                'iva_id'     => $a->iva_id,
                'percentagem_iva' => $a->iva?->percentagem,
                'foto'       => $a->foto ? '/artigos/foto/' . $a->id : null,
                'observacoes' => $a->observacoes,
                'ativo'      => $a->ativo,
            ]),
            'taxas_iva' => Iva::where('ativo', true)->orderBy('percentagem')->get(['id', 'nome', 'percentagem']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'referencia'  => ['required', 'string', 'max:100', 'unique:artigos,referencia'],
            'nome'        => ['required', 'string', 'max:255'],
            'descricao'   => ['nullable', 'string'],
            'preco'       => ['required', 'numeric', 'min:0'],
            'iva_id'      => ['nullable', 'exists:iva,id'],
            'foto'        => ['nullable', 'image', 'max:2048'],
            'observacoes' => ['nullable', 'string'],
            'ativo'       => ['boolean'],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('artigos', 'private');
        }

        Artigo::create($validated);
        return back()->with('success', 'Artigo criado.');
    }

    public function update(Request $request, Artigo $artigo)
    {
        $validated = $request->validate([
            'referencia'  => ['required', 'string', 'max:100', 'unique:artigos,referencia,' . $artigo->id],
            'nome'        => ['required', 'string', 'max:255'],
            'descricao'   => ['nullable', 'string'],
            'preco'       => ['required', 'numeric', 'min:0'],
            'iva_id'      => ['nullable', 'exists:iva,id'],
            'foto'        => ['nullable', 'image', 'max:2048'],
            'observacoes' => ['nullable', 'string'],
            'ativo'       => ['boolean'],
        ]);

        if ($request->hasFile('foto')) {
            if ($artigo->foto) Storage::disk('private')->delete($artigo->foto);
            $validated['foto'] = $request->file('foto')->store('artigos', 'private');
        }

        $artigo->update($validated);
        return back()->with('success', 'Artigo atualizado.');
    }

    public function destroy(Artigo $artigo)
    {
        if ($artigo->foto) Storage::disk('private')->delete($artigo->foto);
        $artigo->delete();
        return back();
    }

    public function foto(Artigo $artigo)
    {
        abort_unless($artigo->foto, 404);
        return response()->file(storage_path('app/private/' . $artigo->foto));
    }
}