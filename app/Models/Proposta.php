<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    // Cabeçalho da proposta comercial.
    protected $table = 'propostas';

    protected $fillable = [
        'numero', 'data', 'entidade_id',
        'validade', 'valor_total', 'estado',
    ];

    protected $casts = [
        'data'        => 'date',
        'validade'    => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function entidade()
    {
        // Cliente associado à proposta.
        return $this->belongsTo(Entidade::class);
    }

    public function linhas()
    {
        // Linhas detalhadas que compõem o valor total.
        return $this->hasMany(PropostaLinha::class);
    }

    public static function proximoNumero()
    {
        // Calcula o próximo número sequencial disponível.
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        // Recalcula o total a partir das linhas gravadas.
        $total = $this->linhas->sum(fn($l) => $l->subtotal);
        $this->update(['valor_total' => $total]);
    }
}