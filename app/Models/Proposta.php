<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
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
        return $this->belongsTo(Entidade::class);
    }

    public function linhas()
    {
        return $this->hasMany(PropostaLinha::class);
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        $total = $this->linhas->sum(fn($l) => $l->subtotal);
        $this->update(['valor_total' => $total]);
    }
}