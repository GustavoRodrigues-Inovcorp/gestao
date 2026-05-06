<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = 'iva';
    protected $fillable = ['nome', 'percentagem', 'ativo'];
    protected $casts = ['ativo' => 'boolean', 'percentagem' => 'decimal:2'];
}