<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
class EncomendaLinha extends Model
{
    use BelongsToTenant;
    // Linha de venda associada a uma encomenda.
    protected $table = 'encomenda_linhas';

    protected $fillable = [
        'encomenda_id', 'artigo_id', 'fornecedor_id',
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
        // Artigo opcional associado à linha.
        return $this->belongsTo(Artigo::class);
    }

    public function fornecedor()
    {
        // Fornecedor opcional usado na conversão para compras.
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }
}