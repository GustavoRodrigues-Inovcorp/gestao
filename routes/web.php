<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ViesController;
use App\Http\Controllers\EntidadeController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\PropostaController;
use App\Http\Controllers\EncomendaController;
use App\Http\Controllers\EncomendaFornecedorController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ArquivoDigitalController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\OrdemTrabalhoController;
use App\Http\Controllers\Configuracoes\EmpresaController;
use App\Http\Controllers\Configuracoes\PaisController;
use App\Http\Controllers\Configuracoes\ContactoFuncaoController;
use App\Http\Controllers\Configuracoes\IvaController;
use App\Http\Controllers\Configuracoes\CalendarioTipoController;
use App\Http\Controllers\Configuracoes\CalendarioAcaoController;
use App\Http\Controllers\Configuracoes\ArtigoController;
use App\Http\Controllers\Acessos\PermissaoController;
use App\Http\Controllers\Acessos\UtilizadorController;
use App\Http\Controllers\Financeiro\FaturaFornecedorController;
use App\Http\Controllers\Financeiro\ContaBancariaController;
use App\Http\Controllers\Financeiro\ContaCorrenteClienteController;

// Página inicial
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// Logotipo da empresa
Route::get('/empresa/logotipo', function () {
    $empresa = \App\Models\Empresa::first();
    abort_unless($empresa?->logotipo, 404);
    return response()->file(storage_path('app/private/' . $empresa->logotipo));
})->middleware(['auth'])->name('empresa.logotipo');

Route::middleware(['auth'])->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // VIES
    Route::get('/vies/lookup', [ViesController::class, 'lookup'])->name('vies.lookup');

    // Entidades
    Route::get('/clientes', [EntidadeController::class, 'clientes'])->name('clientes');
    Route::get('/fornecedores', [EntidadeController::class, 'fornecedores'])->name('fornecedores');
    Route::post('/entidades', [EntidadeController::class, 'store'])->name('entidades.store');
    Route::put('/entidades/{entidade}', [EntidadeController::class, 'update'])->name('entidades.update');
    Route::delete('/entidades/{entidade}', [EntidadeController::class, 'destroy'])->name('entidades.destroy');
    Route::get('/entidades/check-nif', [EntidadeController::class, 'checkNif'])->name('entidades.check-nif');

    // Contactos
    Route::resource('contactos', ContactoController::class)
        ->except(['create', 'edit', 'show']);

    // Propostas
    Route::resource('propostas', PropostaController::class)
        ->except(['create', 'edit', 'show']);
    Route::get('/propostas/{proposta}/pdf', [PropostaController::class, 'pdf'])
        ->name('propostas.pdf');
    Route::post('/propostas/{proposta}/converter', [PropostaController::class, 'converter'])
        ->name('propostas.converter');
    Route::get('/propostas/{proposta}/linhas', function (\App\Models\Proposta $proposta) {
        return $proposta->linhas->map(fn($l) => [
            'artigo_id'     => $l->artigo_id,
            'fornecedor_id' => $l->fornecedor_id,
            'referencia'    => $l->referencia,
            'nome'          => $l->nome,
            'quantidade'    => $l->quantidade,
            'preco_venda'   => $l->preco_venda,
            'preco_custo'   => $l->preco_custo,
            'iva'           => $l->iva,
        ]);
    })->name('propostas.linhas');

    // Encomendas Clientes
    Route::get('/encomendas/clientes', [EncomendaController::class, 'index'])->name('encomendas.clientes');
    Route::resource('encomendas', EncomendaController::class)
        ->except(['create', 'edit', 'show', 'index']);
    Route::get('/encomendas/{encomenda}/pdf', [EncomendaController::class, 'pdf'])->name('encomendas.pdf');
    Route::post('/encomendas/{encomenda}/converter-fornecedor', [EncomendaController::class, 'converterParaFornecedor'])->name('encomendas.converter-fornecedor');
    Route::get('/encomendas/{encomenda}/linhas', [EncomendaController::class, 'linhas'])->name('encomendas.linhas');

    // Encomendas Fornecedores
    Route::get('/encomendas/fornecedores', [EncomendaFornecedorController::class, 'index'])->name('encomendas.fornecedores');
    Route::resource('encomendas-fornecedor', EncomendaFornecedorController::class)
        ->except(['create', 'edit', 'show', 'index']);
    Route::get('/encomendas-fornecedor/{encomendaFornecedor}/pdf', [EncomendaFornecedorController::class, 'pdf'])->name('encomendas-fornecedor.pdf');
    Route::get('/encomendas-fornecedor/{encomendaFornecedor}/linhas', [EncomendaFornecedorController::class, 'linhas'])->name('encomendas-fornecedor.linhas');
    
    // Calendário
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');
    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');
    Route::post('/calendario/eventos', [CalendarioController::class, 'store'])->name('calendario.store');
    Route::put('/calendario/eventos/{evento}', [CalendarioController::class, 'update'])->name('calendario.update');
    Route::delete('/calendario/eventos/{evento}', [CalendarioController::class, 'destroy'])->name('calendario.destroy');

    // Arquivo Digital
    Route::get('/arquivo-digital', [ArquivoDigitalController::class, 'index'])->name('arquivo-digital');
    Route::post('/arquivo-digital', [ArquivoDigitalController::class, 'store'])->name('arquivo-digital.store');
    Route::get('/arquivo-digital/{arquivoDigital}/download', [ArquivoDigitalController::class, 'download'])->name('arquivo-digital.download');
    Route::delete('/arquivo-digital/{arquivoDigital}', [ArquivoDigitalController::class, 'destroy'])->name('arquivo-digital.destroy');

    // Gestão de Acessos
    Route::prefix('acessos')->group(function () {
        Route::resource('permissoes', PermissaoController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('utilizadores', UtilizadorController::class)
            ->except(['create', 'edit', 'show'])
            ->parameters(['utilizadores' => 'utilizador']);
    });

    // Ordens de Trabalho
    Route::resource('ordens-trabalho', OrdemTrabalhoController::class)
        ->except(['create', 'edit', 'show']);
    Route::get('/ordens-trabalho/{ordemTrabalho}/linhas', [OrdemTrabalhoController::class, 'linhas'])->name('ordens-trabalho.linhas');

    // Financeiro
    Route::prefix('financeiro')->group(function () {
        Route::resource('faturas-fornecedor', FaturaFornecedorController::class)
            ->except(['create', 'edit', 'show']);
        Route::post('/faturas-fornecedor/{faturaFornecedor}/comprovativo', [FaturaFornecedorController::class, 'enviarComprovativo'])
            ->name('faturas-fornecedor.comprovativo');
        Route::get('/documento/{faturaFornecedor}', [FaturaFornecedorController::class, 'documento'])
            ->name('faturas-fornecedor.documento');
        Route::get('/comprovativo/{faturaFornecedor}', [FaturaFornecedorController::class, 'comprovativo'])
            ->name('faturas-fornecedor.comprovativo-download');

        Route::resource('contas-bancarias', ContaBancariaController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('conta-corrente-clientes', ContaCorrenteClienteController::class)
            ->except(['create', 'edit', 'show']);
    });

    // Configurações
    Route::prefix('configuracoes')->group(function () {
        Route::get('/empresa', [EmpresaController::class, 'show'])->name('configuracoes.empresa');
        Route::post('/empresa', [EmpresaController::class, 'update'])->name('configuracoes.empresa.update');

        Route::resource('paises', PaisController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('funcoes', ContactoFuncaoController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('iva', IvaController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('calendario-tipos', CalendarioTipoController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('calendario-acoes', CalendarioAcaoController::class)
            ->except(['create', 'edit', 'show']);
        Route::resource('artigos', ArtigoController::class)
            ->except(['create', 'edit', 'show']);
        Route::get('/artigos/foto/{artigo}', [ArtigoController::class, 'foto'])
            ->name('configuracoes.artigos.foto');

        Route::get('/logs', [LogsController::class, 'index'])->name('configuracoes.logs');
    });

});

require __DIR__.'/auth.php';