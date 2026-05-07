<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artigo extends Model
{
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
        return $this->belongsTo(Iva::class);
    }
}