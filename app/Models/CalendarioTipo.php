<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CalendarioTipo extends Model
{
    protected $table = 'calendario_tipos';
    protected $fillable = ['nome', 'cor', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}