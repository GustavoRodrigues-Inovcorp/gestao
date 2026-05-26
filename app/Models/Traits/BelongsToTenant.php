<?php
namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('current_tenant_id')) {
                $builder->where(
                    (new static)->getTable() . '.tenant_id',
                    app('current_tenant_id')
                );
            }
        });

        static::creating(function ($model) {
            if (app()->has('current_tenant_id') && empty($model->tenant_id)) {
                $model->tenant_id = app('current_tenant_id');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    // Permite queries sem o scope de tenant quando necessário
    public static function semFiltroTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}