<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class CalendarioTipo extends Model
{
    use BelongsToTenant;
    
    protected $table = 'calendario_tipos';
    protected $fillable = ['nome', 'cor', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}