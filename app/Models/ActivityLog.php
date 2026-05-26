<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class ActivityLog extends Model
{
    use BelongsToTenant;
    
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'menu', 'acao', 'ip', 'dispositivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}