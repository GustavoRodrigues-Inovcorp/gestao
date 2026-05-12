<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'menu', 'acao', 'ip', 'dispositivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}