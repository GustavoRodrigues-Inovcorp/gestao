<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = [
        'nome', 'slug', 'descricao', 'preco_mensal', 'preco_anual',
        'max_utilizadores', 'max_clientes', 'max_documentos',
        'trial_dias', 'ativo', 'is_free', 'features', 'ordem',
    ];

    protected $casts = [
        'features'       => 'array',
        'ativo'          => 'boolean',
        'is_free'        => 'boolean',
        'preco_mensal'   => 'decimal:2',
        'preco_anual'    => 'decimal:2',
    ];

    public function subscricoes()
    {
        return $this->hasMany(Subscricao::class);
    }
}