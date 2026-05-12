<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaturaFornecedor extends Model
{
    protected $table = 'faturas_fornecedor';

    protected $fillable = [
        'numero', 'data_fatura', 'data_vencimento',
        'fornecedor_id', 'encomenda_fornecedor_id',
        'valor_total', 'documento', 'comprovativo', 'estado',
    ];

    protected $casts = [
        'data_fatura'     => 'date',
        'data_vencimento' => 'date',
        'valor_total'     => 'decimal:2',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }

    public function encomendaFornecedor()
    {
        return $this->belongsTo(EncomendaFornecedor::class);
    }

    public static function proximoNumero()
    {
        return (self::max('numero') ?? 0) + 1;
    }
}