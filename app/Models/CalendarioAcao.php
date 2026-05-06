<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CalendarioAcao extends Model
{
    protected $table = 'calendario_acoes';
    protected $fillable = ['nome', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}