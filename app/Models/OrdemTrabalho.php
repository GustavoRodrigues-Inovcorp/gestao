<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemTrabalho extends Model
{
    protected $table = 'ordens_trabalho';

    protected $fillable = [
        'numero', 'data', 'entidade_id', 'contacto_id',
        'descricao', 'observacoes', 'user_id',
        'estado', 'data_prevista', 'data_conclusao',
    ];

    protected $casts = [
        'data'           => 'date',
        'data_prevista'  => 'date',
        'data_conclusao' => 'date',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function contacto()
    {
        return $this->belongsTo(Contacto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function linhas()
    {
        return $this->hasMany(OrdemTrabalhoLinha::class);
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        return $this->linhas->sum('subtotal');
    }
}