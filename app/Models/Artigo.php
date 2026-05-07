<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artigo extends Model
{
    // Tabela de artigos usada nas propostas e encomendas.
    protected $table = 'artigos';

    protected $fillable = [
        'referencia', 'nome', 'descricao',
        'preco', 'iva_id', 'foto',
        'observacoes', 'ativo',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function iva()
    {
        // Relação com a taxa de IVA associada ao artigo.
        return $this->belongsTo(Iva::class);
    }
}