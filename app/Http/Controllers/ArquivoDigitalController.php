<?php
namespace App\Http\Controllers;

use App\Models\ArquivoDigital;
use App\Models\Entidade;
use App\Services\SubscricaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArquivoDigitalController extends Controller
{
    public function index()
    {
        return Inertia::render('ArquivoDigital/Index', [
            'ficheiros' => ArquivoDigital::with(['entidade', 'user'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($f) => [
                    'id'        => $f->id,
                    'nome'      => $f->nome,
                    'tamanho'   => $f->tamanhoFormatado(),
                    'tipo_mime' => $f->tipo_mime,
                    'entidade'  => $f->entidade?->nome,
                    'entidade_id' => $f->entidade_id,
                    'user'      => $f->user->name,
                    'descricao' => $f->descricao,
                    'data'      => $f->created_at->format('d/m/Y H:i'),
                    'url'       => '/arquivo-digital/' . $f->id . '/download',
                ]),
            'entidades' => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        if ($tenant) {
            app(SubscricaoService::class)->validarLimiteCriacao($tenant, 'documentos');
        }

        $request->validate([
            'nome'        => ['required', 'string', 'max:255'],
            'ficheiro'    => ['required', 'file', 'max:20480'],
            'entidade_id' => ['nullable', 'exists:entidades,id'],
            'descricao'   => ['nullable', 'string'],
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store('arquivo', 'local');

        ArquivoDigital::create([
            'nome'        => $request->input('nome'),
            'ficheiro'    => $path,
            'tipo_mime'   => $file->getMimeType(),
            'tamanho'     => $file->getSize(),
            'entidade_id' => $request->input('entidade_id'),
            'user_id'     => auth()->id(),
            'descricao'   => $request->input('descricao'),
        ]);

        return back()->with('success', 'Ficheiro carregado.');
    }

    public function download(ArquivoDigital $arquivoDigital)
    {
        abort_unless(Storage::disk('local')->exists($arquivoDigital->ficheiro), 404);
        return Storage::disk('local')->download(
            $arquivoDigital->ficheiro,
            $arquivoDigital->nome
        );
    }

    public function destroy(ArquivoDigital $arquivoDigital)
    {
        Storage::disk('local')->delete($arquivoDigital->ficheiro);
        $arquivoDigital->delete();
        return back();
    }
}