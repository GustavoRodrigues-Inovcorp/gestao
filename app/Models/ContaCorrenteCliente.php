<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ContaCorrenteCliente extends Model
{
    use BelongsToTenant;
    
    protected $table = 'conta_corrente_clientes';

    protected $fillable = [
        'entidade_id', 'data', 'descricao',
        'debito', 'credito', 'saldo',
        'tipo', 'referencia', 'user_id',
    ];

    protected $casts = [
        'data'    => 'date',
        'debito'  => 'decimal:2',
        'credito' => 'decimal:2',
        'saldo'   => 'decimal:2',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}