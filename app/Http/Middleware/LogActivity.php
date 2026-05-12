<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        app('activitylog.logger')->setCustomProperty('ip', $request->ip());
        app('activitylog.logger')->setCustomProperty('dispositivo', $request->userAgent());
        return $next($request);
    }
}