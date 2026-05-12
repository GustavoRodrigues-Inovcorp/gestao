<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';

    protected $fillable = [
        'banco', 'iban', 'swift',
        'titular', 'saldo', 'ativo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'ativo' => 'boolean',
    ];
}