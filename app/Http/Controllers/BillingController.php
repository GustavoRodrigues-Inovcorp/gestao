<?php
namespace App\Http\Controllers;

use App\Models\Plano;
use App\Services\SubscricaoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function __construct(private SubscricaoService $service) {}

    public function index()
    {
        $tenant = app('current_tenant');
        $subscricao = $tenant->subscricaoAtiva()->with('plano', 'planoPendente')->first()
            ?? $tenant->subscricao()->with('plano', 'planoPendente')->first();
        $limites = $this->service->verificarLimites($tenant);

        return Inertia::render('Billing/Index', [
            'planos'    => Plano::where('ativo', true)->orderBy('ordem')->get(),
            'subscricao' => $subscricao ? [
                'id'              => $subscricao->id,
                'estado'          => $subscricao->estado,
                'ciclo'           => $subscricao->ciclo,
                'preco'           => $subscricao->preco,
                'trial_fim'       => $subscricao->trial_fim ? $subscricao->trial_fim->format('d/m/Y') : null,
                'dias_trial'      => $subscricao->diasRestantesTrial(),
                'inicio'          => $subscricao->inicio ? $subscricao->inicio->format('d/m/Y') : null,
                'proximo_ciclo'   => $subscricao->proximo_ciclo ? $subscricao->proximo_ciclo->format('d/m/Y') : null,
                'cancelado_em'    => $subscricao->cancelado_em ? $subscricao->cancelado_em->format('d/m/Y') : null,
                'plano'           => $subscricao->plano,
                'plano_pendente'  => $subscricao->planoPendente,
                'cancelamento_agendado' => (bool) $subscricao->cancelado_em
                    && in_array($subscricao->estado, ['ativa', 'trial'], true),
            ] : null,
            'limites' => $limites,
        ]);
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plano_id' => ['required', 'exists:planos,id'],
            'ciclo'    => ['required', 'in:mensal,anual'],
        ]);

        $tenant = app('current_tenant');
        $novoPlano = Plano::findOrFail($request->input('plano_id'));
        $subscricao = $tenant->subscricaoAtiva()->with('plano')->first();

        // Verifica se é upgrade ou downgrade
        $isUpgrade = !$subscricao || $novoPlano->preco_mensal >= $subscricao->plano->preco_mensal;

        if ($isUpgrade) {
            $this->service->upgrade($tenant, $novoPlano, $request->input('ciclo'));
            $msg = "Upgrade para {$novoPlano->nome} realizado com sucesso!";
        } else {
            $this->service->downgrade($tenant, $novoPlano, $request->input('ciclo'));
            $msg = "Downgrade para {$novoPlano->nome} agendado para o próximo ciclo.";
        }

        return back()->with('success', $msg);
    }

    public function cancelar(Request $request)
    {
        $request->validate([
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant = app('current_tenant');
        $this->service->cancelar($tenant, $request->motivo ?? '');

        return back()->with('success', 'Cancelamento agendado. Acesso mantido ate ao fim do ciclo atual.');
    }

    public function iniciarTrial()
    {
        $tenant = app('current_tenant');
        if ($tenant->subscricao()->exists()) {
            return back()->withErrors(['trial' => 'Já tem uma subscrição ativa.']);
        }

        $this->service->iniciarTrial($tenant);
        return back()->with('success', 'Trial iniciado!');
    }
}   