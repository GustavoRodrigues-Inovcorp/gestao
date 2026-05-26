<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class CalendarioAcao extends Model
{
    use BelongsToTenant;
    
    protected $table = 'calendario_acoes';
    protected $fillable = ['nome', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}