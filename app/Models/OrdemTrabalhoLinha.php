<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class OrdemTrabalhoLinha extends Model
{
    use BelongsToTenant;
    protected $table = 'ordem_trabalho_linhas';

    protected $fillable = [
        'ordem_trabalho_id', 'artigo_id',
        'descricao', 'quantidade', 'unidade',
        'preco', 'subtotal',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'preco'      => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function artigo()
    {
        return $this->belongsTo(Artigo::class);
    }
}