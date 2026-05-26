<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Pais extends Model
{
    use BelongsToTenant;
    
    protected $table = 'paises';
    protected $fillable = ['nome', 'codigo', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}