<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Entidade extends Model
{
    use BelongsToTenant;
    
    protected $table = 'entidades';

    protected $fillable = [
        'numero', 'is_cliente', 'is_fornecedor', 'nif', 'nome',
        'morada', 'codigo_postal', 'localidade', 'pais_id',
        'telefone', 'telemovel', 'website', 'email',
        'rgpd', 'observacoes', 'ativo',
    ];

    protected $casts = [
        'is_cliente'    => 'boolean',
        'is_fornecedor' => 'boolean',
        'rgpd'          => 'boolean',
        'ativo'         => 'boolean',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }
}