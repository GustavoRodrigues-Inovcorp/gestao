<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'nome', 'slug', 'logotipo', 'estado', 'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($tenant) {
            if (!$tenant->slug) {
                $tenant->slug = Str::slug($tenant->nome) . '-' . Str::random(6);
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withTimestamps();
    }

    public function empresa()
    {
        return $this->hasOne(Empresa::class);
    }

    public function subscricao()
    {
        return $this->hasOne(Subscricao::class)->latest();
    }

    public function subscricaoAtiva()
    {
        return $this->hasOne(Subscricao::class)
            ->whereIn('estado', ['ativa', 'trial'])
            ->latest();
    }

    public function planoLogs()
    {
        return $this->hasMany(PlanoLog::class);
    }
}