<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class EncomendaFornecedor extends Model
{
    use BelongsToTenant;
    
    // Cabeçalho da encomenda enviada ao fornecedor.
    protected $table = 'encomendas_fornecedor';

    protected $fillable = [
        'numero', 'data', 'validade', 'fornecedor_id',
        'encomenda_id', 'valor_total', 'estado',
    ];

    protected $casts = [
        'data'        => 'date',
        'validade'    => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function fornecedor()
    {
        // Fornecedor destinatário do documento.
        return $this->belongsTo(Entidade::class, 'fornecedor_id');
    }

    public function encomenda()
    {
        // Encomenda de cliente que originou este documento, quando aplicável.
        return $this->belongsTo(Encomenda::class);
    }

    public function linhas()
    {
        // Linhas de custo que compõem o total.
        return $this->hasMany(EncomendaFornecedorLinha::class);
    }

    public static function proximoNumero()
    {
        // Próximo número sequencial do documento.
        return (self::max('numero') ?? 0) + 1;
    }

    public function calcularTotal()
    {
        // Recalcula o valor total a partir das linhas associadas.
        $this->update(['valor_total' => $this->linhas->sum('subtotal')]);
    }
}