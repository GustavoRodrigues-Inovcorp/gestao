<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropostaLinha extends Model
{
    protected $table = 'proposta_linhas';

    protected $fillable = [
        'proposta_id', 'artigo_id', 'fornecedor_id',
        'referencia', 'nome', 'quantidade',
        'preco_venda', 'preco_custo', 'iva', 'subtotal',
    ];

    protected $casts = [
        'preco_venda' => 'decimal:2',
        'preco_custo' => 'decimal:2',
        'iva'         => 'decimal:2',
        'subtotal'    => 'decimal:2',
    ];

    public function artigo()
    {
        return $this->belongsTo(Artigo::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }
}