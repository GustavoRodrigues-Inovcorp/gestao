<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encomenda extends Model
{
    protected $table = 'encomendas';

    protected $fillable = [
        'numero', 'data', 'entidade_id', 'proposta_id',
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

    public function proposta()
    {
        return $this->belongsTo(Proposta::class);
    }

    public function linhas()
    {
        return $this->hasMany(EncomendaLinha::class);
    }

    public function encomendasFornecedor()
    {
        return $this->hasMany(EncomendaFornecedor::class);
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        $this->update(['valor_total' => $this->linhas->sum('subtotal')]);
    }
}