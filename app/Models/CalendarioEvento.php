<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class CalendarioEvento extends Model
{
    use BelongsToTenant;
    protected $table = 'calendario_eventos';

    protected $fillable = [
        'titulo', 'inicio', 'fim', 'duracao',
        'user_id', 'entidade_id', 'tipo_id', 'acao_id',
        'descricao', 'partilha', 'conhecimento', 'estado',
    ];

    protected $casts = [
        'inicio'      => 'datetime:Y-m-d H:i:s',
        'fim'         => 'datetime:Y-m-d H:i:s',
        'partilha'    => 'boolean',
        'conhecimento' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function tipo()
    {
        return $this->belongsTo(CalendarioTipo::class, 'tipo_id');
    }

    public function acao()
    {
        return $this->belongsTo(CalendarioAcao::class, 'acao_id');
    }
}