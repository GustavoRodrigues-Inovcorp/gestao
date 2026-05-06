<?php

use App\Http\Controllers\Configuracoes\EmpresaController;
use App\Http\Controllers\Configuracoes\PaisController;
use App\Http\Controllers\Configuracoes\ContactoFuncaoController;
use App\Http\Controllers\Configuracoes\CalendarioTipoController;
use App\Http\Controllers\Configuracoes\CalendarioAcaoController;
use App\Http\Controllers\Configuracoes\IvaController;
use App\Http\Controllers\Acessos\PermissaoController;
use App\Http\Controllers\Acessos\UtilizadorController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\EntidadeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/empresa/logotipo', function () {
    $empresa = \App\Models\Empresa::first();
    abort_unless($empresa?->logotipo, 404);
    return response()->file(storage_path('app/private/' . $empresa->logotipo));
})->middleware(['auth'])->name('empresa.logotipo');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth'])->prefix('configuracoes')->group(function () {
    Route::get('/empresa', [EmpresaController::class, 'show'])->name('configuracoes.empresa');
    Route::post('/empresa', [EmpresaController::class, 'update'])->name('configuracoes.empresa.update');

    Route::resource('paises', PaisController::class)->except(['create', 'edit', 'show']);
    Route::resource('funcoes', ContactoFuncaoController::class)->except(['create', 'edit', 'show']);
    Route::resource('iva', IvaController::class)->except(['create', 'edit', 'show']);

    Route::resource('calendario-tipos', CalendarioTipoController::class)
        ->except(['create', 'edit', 'show']);
    Route::resource('calendario-acoes', CalendarioAcaoController::class)
        ->except(['create', 'edit', 'show']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/clientes', [EntidadeController::class, 'clientes'])->name('clientes');
    Route::get('/fornecedores', [EntidadeController::class, 'fornecedores'])->name('fornecedores');
    Route::get('/contactos', [ContactoController::class, 'index'])->name('contactos');
    Route::post('/entidades', [EntidadeController::class, 'store'])->name('entidades.store');
    Route::put('/entidades/{entidade}', [EntidadeController::class, 'update'])->name('entidades.update');
    Route::delete('/entidades/{entidade}', [EntidadeController::class, 'destroy'])->name('entidades.destroy');
    Route::get('/entidades/check-nif', [EntidadeController::class, 'checkNif'])->name('entidades.check-nif');

    Route::post('/contactos', [ContactoController::class, 'store'])->name('contactos.store');
    Route::put('/contactos/{contacto}', [ContactoController::class, 'update'])->name('contactos.update');
    Route::delete('/contactos/{contacto}', [ContactoController::class, 'destroy'])->name('contactos.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('acessos')->group(function () {
        Route::resource('permissoes', PermissaoController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('utilizadores', UtilizadorController::class)
            ->except(['create', 'edit', 'show'])
            ->parameters(['utilizadores' => 'utilizador']);
    });
});

require __DIR__.'/auth.php';
