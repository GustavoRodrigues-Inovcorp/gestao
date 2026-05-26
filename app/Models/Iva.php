<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Iva extends Model
{
    use BelongsToTenant;
    
    protected $table = 'iva';
    protected $fillable = ['nome', 'percentagem', 'ativo'];
    protected $casts = ['ativo' => 'boolean', 'percentagem' => 'decimal:2'];
}