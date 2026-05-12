<?php
namespace App\Listeners;

use App\Helpers\LogHelper;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;

class LogAuthActivity
{
    public function handleLogin(Login $event): void
    {
        Auth::setUser($event->user);
        LogHelper::log('Autenticação', "Login: {$event->user->name}");
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            LogHelper::log('Autenticação', "Logout: {$event->user->name}");
        }
    }
}