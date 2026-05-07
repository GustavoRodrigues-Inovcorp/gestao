<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encomenda extends Model
{
    // Cabeçalho da encomenda do cliente.
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
        // Cliente destinatário da encomenda.
        return $this->belongsTo(Entidade::class);
    }

    public function proposta()
    {
        // Proposta de origem, quando a encomenda nasce por conversão.
        return $this->belongsTo(Proposta::class);
    }

    public function linhas()
    {
        // Linhas de venda da encomenda.
        return $this->hasMany(EncomendaLinha::class);
    }

    public function encomendasFornecedor()
    {
        // Encomendas de fornecedor geradas a partir desta encomenda.
        return $this->hasMany(EncomendaFornecedor::class);
    }

    public static function proximoNumero()
    {
        // Sequência numérica visível no documento.
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        // Soma os subtotais das linhas já guardadas.
        $this->update(['valor_total' => $this->linhas->sum('subtotal')]);
    }
}