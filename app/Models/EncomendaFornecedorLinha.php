<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncomendaFornecedorLinha extends Model
{
    protected $table = 'encomenda_fornecedor_linhas';

    protected $fillable = [
        'encomenda_fornecedor_id', 'artigo_id',
        'referencia', 'nome', 'quantidade',
        'preco_custo', 'iva', 'subtotal',
    ];

    protected $casts = [
        'preco_custo' => 'decimal:2',
        'iva'         => 'decimal:2',
        'subtotal'    => 'decimal:2',
    ];

    public function artigo()
    {
        return $this->belongsTo(Artigo::class);
    }
}