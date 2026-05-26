<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ContactoFuncao extends Model
{
    use BelongsToTenant;
    
    protected $table = 'contactos_funcoes';
    protected $fillable = ['nome', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];

    public function contactos()
    {
        return $this->hasMany(Contacto::class, 'funcao_id');
    }
}