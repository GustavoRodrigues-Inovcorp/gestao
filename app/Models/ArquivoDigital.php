<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ArquivoDigital extends Model
{
    use BelongsToTenant;
    
    protected $table = 'arquivo_digital';

    protected $fillable = [
        'nome', 'ficheiro', 'tipo_mime',
        'tamanho', 'entidade_id', 'user_id', 'descricao',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tamanhoFormatado()
    {
        $bytes = $this->tamanho;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}