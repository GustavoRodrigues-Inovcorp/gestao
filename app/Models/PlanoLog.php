<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'acao',
        'plano_anterior_id', 'plano_novo_id',
        'valor_pago', 'notas',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function planoAnterior() { return $this->belongsTo(Plano::class, 'plano_anterior_id'); }
    public function planoNovo() { return $this->belongsTo(Plano::class, 'plano_novo_id'); }
}