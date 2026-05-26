<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscricao extends Model
{
    protected $table = 'subscricoes';
    protected $fillable = [
        'tenant_id', 'plano_id', 'estado', 'ciclo', 'preco',
        'trial_inicio', 'trial_fim', 'inicio', 'fim',
        'proximo_ciclo', 'cancelado_em', 'cancelamento_motivo',
        'plano_pendente_id', 'trial_aviso_enviado_em',
    ];

    protected $casts = [
        'trial_inicio'   => 'datetime',
        'trial_fim'      => 'datetime',
        'inicio'         => 'datetime',
        'fim'            => 'datetime',
        'proximo_ciclo'  => 'datetime',
        'cancelado_em'   => 'datetime',
        'trial_aviso_enviado_em' => 'datetime',
        'preco'          => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function planoPendente()
    {
        return $this->belongsTo(Plano::class, 'plano_pendente_id');
    }

    public function estaEmTrial(): bool
    {
        return $this->estado === 'trial' && $this->trial_fim?->isFuture();
    }

    public function trialExpirou(): bool
    {
        return $this->estado === 'trial' && $this->trial_fim?->isPast();
    }

    public function diasRestantesTrial(): int
    {
        if (!$this->estaEmTrial()) return 0;
        return max(0, now()->diffInDays($this->trial_fim, false));
    }

    public function estaAtiva(): bool
    {
        return in_array($this->estado, ['ativa', 'trial']);
    }

    public function calcularProrrata(Plano $novoPlano, string $ciclo = 'mensal'): float
    {
        if (!$this->proximo_ciclo) return 0;
        $diasRestantes = now()->diffInDays($this->proximo_ciclo, false);
        $totalDias = $ciclo === 'anual' ? 365 : 30;
        $precoDiario = ($ciclo === 'anual' ? $novoPlano->preco_anual : $novoPlano->preco_mensal) / $totalDias;
        return round($diasRestantes * $precoDiario, 2);
    }
}