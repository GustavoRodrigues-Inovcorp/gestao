<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\CalendarioEvento;
use App\Models\CalendarioTipo;
use App\Models\CalendarioAcao;
use App\Models\Entidade;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioController extends Controller
{
    public function index()
    {
        return Inertia::render('Calendario/Index', [
            'tipos'      => CalendarioTipo::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'cor']),
            'acoes'      => CalendarioAcao::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'entidades'  => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'utilizadores' => User::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function eventos(Request $request)
    {
        $query = CalendarioEvento::with(['user', 'entidade', 'tipo', 'acao']);

        // Filtros
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->entidade_id) {
            $query->where('entidade_id', $request->entidade_id);
        }

        // Só mostra eventos do utilizador ou partilhados
        $query->where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('partilha', true);
        });

        return $query->get()->map(fn($e) => [
        'id'    => $e->id,
        'title' => $e->titulo,
        'start' => $e->inicio->format('Y-m-d\TH:i:s'),
        'end'   => $e->fim && $e->fim != $e->inicio ? $e->fim->format('Y-m-d\TH:i:s') : null,
        'color' => $e->tipo?->cor ?? '#3b82f6',
        'extendedProps' => [
            'descricao'    => $e->descricao,
            'estado'       => $e->estado,
            'entidade'     => $e->entidade?->nome,
            'entidade_id'  => $e->entidade_id,
            'tipo'         => $e->tipo?->nome,
            'tipo_id'      => $e->tipo_id,
            'acao'         => $e->acao?->nome,
            'acao_id'      => $e->acao_id,
            'user'         => $e->user->name,
            'user_id'      => $e->user_id,
            'partilha'     => $e->partilha,
            'duracao'      => $e->duracao,
        ],
]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'       => ['required', 'string', 'max:255'],
            'inicio'       => ['required', 'date'],
            'fim'          => ['nullable', 'date'],
            'duracao'      => ['nullable', 'integer', 'min:1'],
            'entidade_id'  => ['nullable', 'exists:entidades,id'],
            'tipo_id'      => ['nullable', 'exists:calendario_tipos,id'],
            'acao_id'      => ['nullable', 'exists:calendario_acoes,id'],
            'descricao'    => ['nullable', 'string'],
            'partilha'     => ['boolean'],
            'estado'       => ['required', 'in:pendente,concluido,cancelado'],
        ]);

        $validated['user_id'] = auth()->id();

        // Garante que fim é sempre posterior ao início
        if (!empty($validated['fim']) && $validated['fim'] <= $validated['inicio']) {
            $validated['fim'] = null;
        }

        CalendarioEvento::create($validated);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, CalendarioEvento $evento)
    {
        $validated = $request->validate([
            'titulo'       => ['required', 'string', 'max:255'],
            'inicio'       => ['required', 'date'],
            'fim'          => ['nullable', 'date', 'after_or_equal:inicio'],
            'duracao'      => ['nullable', 'integer', 'min:1'],
            'entidade_id'  => ['nullable', 'exists:entidades,id'],
            'tipo_id'      => ['nullable', 'exists:calendario_tipos,id'],
            'acao_id'      => ['nullable', 'exists:calendario_acoes,id'],
            'descricao'    => ['nullable', 'string'],
            'partilha'     => ['boolean'],
            'estado'       => ['required', 'in:pendente,concluido,cancelado'],
        ]);

        $evento->update($validated);
        LogHelper::log('Calendário', "Atualizou evento: {$evento->titulo}");
        return response()->json(['success' => true]);
    }

    public function destroy(CalendarioEvento $evento)
    {
        LogHelper::log('Calendário', "Eliminou evento: {$evento->titulo}");
        $evento->delete();
        return response()->json(['success' => true]);
    }
}