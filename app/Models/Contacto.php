<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'contactos';

    protected $fillable = [
        'numero',
        'entidade_id',
        'nome',
        'apelido',
        'funcao_id',
        'telefone',
        'telemovel',
        'email',
        'rgpd',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'rgpd' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function funcao()
    {
        return $this->belongsTo(ContactoFuncao::class, 'funcao_id');
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }
}
