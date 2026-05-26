<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Empresa extends Model
{
    use BelongsToTenant;
    protected $table = 'empresa';

    protected $fillable = [
        'nome',
        'morada',
        'codigo_postal',
        'localidade',
        'nif',
        'logotipo',
    ];
}