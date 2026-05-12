<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!class_exists(\Spatie\Activitylog\Models\Activity::class)) {
            return;
        }

        \Spatie\Activitylog\Models\Activity::saving(function ($activity) {
            $request = request();
            $activity->properties = $activity->properties->merge([
                'ip'          => $request->ip(),
                'dispositivo' => substr($request->userAgent() ?? '', 0, 200),
            ]);
        });
    }
}