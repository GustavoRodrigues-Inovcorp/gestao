<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncomendaFornecedor extends Model
{
    protected $table = 'encomendas_fornecedor';

    protected $fillable = [
        'numero', 'data', 'fornecedor_id',
        'encomenda_id', 'valor_total', 'estado',
    ];

    protected $casts = [
        'data'        => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }

    public function encomenda()
    {
        return $this->belongsTo(Encomenda::class);
    }

    public function linhas()
    {
        return $this->hasMany(EncomendaFornecedorLinha::class);
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