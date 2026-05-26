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
use App\Http\Controllers\TenantController;
use App\Http\Controllers\BillingController;
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
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome');
})->name('welcome');

// Dashboard
Route::get('/dashboard', function () {
    $user = auth()->user();
    $isAdmin = $user->hasAnyRole(['admin', 'Administrador']);

    // Admin vê tudo, outros veem o que têm permissão
    $canSee = fn($perm) => $isAdmin || $user->hasPermissionTo($perm);

    $stats = [];
    if ($canSee('clientes.read'))              $stats['clientes']           = \App\Models\Entidade::where('is_cliente', true)->count();
    if ($canSee('fornecedores.read'))          $stats['fornecedores']        = \App\Models\Entidade::where('is_fornecedor', true)->count();
    if ($canSee('propostas.read'))             $stats['propostas']           = \App\Models\Proposta::count();
    if ($canSee('encomendas_clientes.read'))   $stats['encomendas']          = \App\Models\Encomenda::count();
    if ($canSee('ordens_trabalho.read'))       $stats['ordens']              = \App\Models\OrdemTrabalho::where('estado', 'aberta')->count();
    if ($canSee('financeiro.read'))            $stats['faturas_pendentes']   = \App\Models\FaturaFornecedor::where('estado', 'pendente')->count();

    $propostas_recentes = [];
    if ($canSee('propostas.read')) {
        $propostas_recentes = \App\Models\Proposta::with('entidade')
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn($p) => [
                'numero'      => $p->numero,
                'cliente'     => $p->entidade->nome,
                'valor_total' => $p->valor_total,
                'estado'      => $p->estado,
                'data'        => $p->created_at->format('d/m/Y'),
            ])->toArray();
    }

    $ordens_abertas = [];
    if ($canSee('ordens_trabalho.read')) {
        $ordens_abertas = \App\Models\OrdemTrabalho::with('entidade')
            ->where('estado', 'aberta')
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn($o) => [
                'numero'   => $o->numero,
                'cliente'  => $o->entidade->nome,
                'data'     => $o->data->format('d/m/Y'),
                'prevista' => $o->data_prevista?->format('d/m/Y'),
            ])->toArray();
    }

    return Inertia::render('Dashboard', [
        'stats'              => $stats,
        'propostas_recentes' => $propostas_recentes,
        'ordens_abertas'     => $ordens_abertas,
    ]);
})->middleware(['auth'])->name('dashboard');

// Logotipo da empresa
Route::get('/empresa/logotipo', function () {
    $tenantId = session('tenant_id');
    $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
    $tenantId = $tenant?->id ?? session('tenant_id');
    $empresa = \App\Models\Empresa::where('tenant_id', $tenantId)->first();
    abort_unless((bool) $empresa?->logotipo, 404);
    return response()->file(storage_path('app/private/' . $empresa->logotipo));
})->name('empresa.logotipo');

Route::middleware(['auth'])->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // VIES
    Route::get('/vies/lookup', [ViesController::class, 'lookup'])->name('vies.lookup');

    // Entidades
    Route::get('/clientes', [EntidadeController::class, 'clientes'])->middleware('permission:clientes.read')->name('clientes');
    Route::get('/fornecedores', [EntidadeController::class, 'fornecedores'])->middleware('permission:fornecedores.read')->name('fornecedores');
    Route::post('/entidades', [EntidadeController::class, 'store'])->middleware('permission:clientes.create|fornecedores.create')->name('entidades.store');
    Route::put('/entidades/{entidade}', [EntidadeController::class, 'update'])->middleware('permission:clientes.update|fornecedores.update')->name('entidades.update');
    Route::delete('/entidades/{entidade}', [EntidadeController::class, 'destroy'])->middleware('permission:clientes.delete|fornecedores.delete')->name('entidades.destroy');
    Route::get('/entidades/check-nif', [EntidadeController::class, 'checkNif'])->name('entidades.check-nif');

    // Contactos
    Route::resource('contactos', ContactoController::class)
        ->middleware('permission:contactos.read')
        ->middlewareFor('store', 'permission:contactos.create')
        ->middlewareFor('update', 'permission:contactos.update')
        ->middlewareFor('destroy', 'permission:contactos.delete')
        ->except(['create', 'edit', 'show']);

    // Propostas
    Route::resource('propostas', PropostaController::class)
        ->middleware('permission:propostas.read')
        ->middlewareFor('store', 'permission:propostas.create')
        ->middlewareFor('update', 'permission:propostas.update')
        ->middlewareFor('destroy', 'permission:propostas.delete')
        ->except(['create', 'edit', 'show']);
    Route::get('/propostas/{proposta}/pdf', [PropostaController::class, 'pdf'])
        ->middleware('permission:propostas.read')
        ->name('propostas.pdf');
    Route::post('/propostas/{proposta}/converter', [PropostaController::class, 'converter'])
        ->middleware('permission:propostas.update')
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
    })->middleware('permission:propostas.read')->name('propostas.linhas');

    // Encomendas Clientes
    Route::get('/encomendas/clientes', [EncomendaController::class, 'index'])->middleware('permission:encomendas_clientes.read')->name('encomendas.clientes');
    Route::resource('encomendas', EncomendaController::class)
        ->middleware('permission:encomendas_clientes.read')
        ->middlewareFor('store', 'permission:encomendas_clientes.create')
        ->middlewareFor('update', 'permission:encomendas_clientes.update')
        ->middlewareFor('destroy', 'permission:encomendas_clientes.delete')
        ->except(['create', 'edit', 'show', 'index']);
    Route::get('/encomendas/{encomenda}/pdf', [EncomendaController::class, 'pdf'])->middleware('permission:encomendas_clientes.read')->name('encomendas.pdf');
    Route::post('/encomendas/{encomenda}/converter-fornecedor', [EncomendaController::class, 'converterParaFornecedor'])->middleware('permission:encomendas_clientes.update')->name('encomendas.converter-fornecedor');
    Route::get('/encomendas/{encomenda}/linhas', [EncomendaController::class, 'linhas'])->middleware('permission:encomendas_clientes.read')->name('encomendas.linhas');

    // Encomendas Fornecedores
    Route::get('/encomendas/fornecedores', [EncomendaFornecedorController::class, 'index'])->middleware('permission:encomendas_fornecedores.read')->name('encomendas.fornecedores');
    Route::resource('encomendas-fornecedor', EncomendaFornecedorController::class)
        ->middleware('permission:encomendas_fornecedores.read')
        ->middlewareFor('store', 'permission:encomendas_fornecedores.create')
        ->middlewareFor('update', 'permission:encomendas_fornecedores.update')
        ->middlewareFor('destroy', 'permission:encomendas_fornecedores.delete')
        ->except(['create', 'edit', 'show', 'index'])
        ->parameters(['encomendas-fornecedor' => 'encomendaFornecedor']);
    Route::get('/encomendas-fornecedor/{encomendaFornecedor}/pdf', [EncomendaFornecedorController::class, 'pdf'])->middleware('permission:encomendas_fornecedores.read')->name('encomendas-fornecedor.pdf');
    Route::get('/encomendas-fornecedor/{encomendaFornecedor}/linhas', [EncomendaFornecedorController::class, 'linhas'])->middleware('permission:encomendas_fornecedores.read')->name('encomendas-fornecedor.linhas');
    
    // Calendário
    Route::get('/calendario', [CalendarioController::class, 'index'])->middleware('permission:calendario.read')->name('calendario');
    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])->middleware('permission:calendario.read')->name('calendario.eventos');
    Route::post('/calendario/eventos', [CalendarioController::class, 'store'])->middleware('permission:calendario.create')->name('calendario.store');
    Route::put('/calendario/eventos/{evento}', [CalendarioController::class, 'update'])->middleware('permission:calendario.update')->name('calendario.update');
    Route::delete('/calendario/eventos/{evento}', [CalendarioController::class, 'destroy'])->middleware('permission:calendario.delete')->name('calendario.destroy');

    // Arquivo Digital
    Route::get('/arquivo-digital', [ArquivoDigitalController::class, 'index'])->middleware('permission:arquivo_digital.read')->name('arquivo-digital');
    Route::post('/arquivo-digital', [ArquivoDigitalController::class, 'store'])->middleware('permission:arquivo_digital.create')->name('arquivo-digital.store');
    Route::get('/arquivo-digital/{arquivoDigital}/download', [ArquivoDigitalController::class, 'download'])->middleware('permission:arquivo_digital.read')->name('arquivo-digital.download');
    Route::delete('/arquivo-digital/{arquivoDigital}', [ArquivoDigitalController::class, 'destroy'])->middleware('permission:arquivo_digital.delete')->name('arquivo-digital.destroy');

    // Gestão de Acessos
    Route::prefix('acessos')->group(function () {
        Route::resource('permissoes', PermissaoController::class)
            ->middleware('permission:permissoes.read')
            ->middlewareFor('store', 'permission:permissoes.create')
            ->middlewareFor('update', 'permission:permissoes.update')
            ->middlewareFor('destroy', 'permission:permissoes.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['permissoes' => 'role']);
        Route::resource('utilizadores', UtilizadorController::class)
            ->middleware('permission:utilizadores.read')
            ->middlewareFor('store', 'permission:utilizadores.create')
            ->middlewareFor('update', 'permission:utilizadores.update')
            ->middlewareFor('destroy', 'permission:utilizadores.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['utilizadores' => 'utilizador']);
    });

    // Ordens de Trabalho
    Route::resource('ordens-trabalho', OrdemTrabalhoController::class)
        ->middleware('permission:ordens_trabalho.read')
        ->middlewareFor('store', 'permission:ordens_trabalho.create')
        ->middlewareFor('update', 'permission:ordens_trabalho.update')
        ->middlewareFor('destroy', 'permission:ordens_trabalho.delete')
        ->except(['create', 'edit', 'show'])
        ->parameters(['ordens-trabalho' => 'ordemTrabalho']);
    Route::get('/ordens-trabalho/{ordemTrabalho}/linhas', [OrdemTrabalhoController::class, 'linhas'])->middleware('permission:ordens_trabalho.read')->name('ordens-trabalho.linhas');

    // Financeiro
    Route::prefix('financeiro')->group(function () {
        Route::resource('faturas-fornecedor', FaturaFornecedorController::class)
            ->middleware('permission:financeiro.read')
            ->middlewareFor('store', 'permission:financeiro.create')
            ->middlewareFor('update', 'permission:financeiro.update')
            ->middlewareFor('destroy', 'permission:financeiro.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['faturas-fornecedor' => 'faturaFornecedor']);
        Route::post('/faturas-fornecedor/{faturaFornecedor}/comprovativo', [FaturaFornecedorController::class, 'enviarComprovativo'])
            ->middleware('permission:financeiro.update')
            ->name('faturas-fornecedor.comprovativo');
        Route::get('/documento/{faturaFornecedor}', [FaturaFornecedorController::class, 'documento'])
            ->middleware('permission:financeiro.read')
            ->name('faturas-fornecedor.documento');
        Route::get('/comprovativo/{faturaFornecedor}', [FaturaFornecedorController::class, 'comprovativo'])
            ->middleware('permission:financeiro.read')
            ->name('faturas-fornecedor.comprovativo-download');

        Route::resource('contas-bancarias', ContaBancariaController::class)
            ->middleware('permission:financeiro.read')
            ->middlewareFor('store', 'permission:financeiro.create')
            ->middlewareFor('update', 'permission:financeiro.update')
            ->middlewareFor('destroy', 'permission:financeiro.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['contas-bancarias' => 'contaBancaria']);
        Route::resource('conta-corrente-clientes', ContaCorrenteClienteController::class)
            ->middleware('permission:financeiro.read')
            ->middlewareFor('store', 'permission:financeiro.create')
            ->middlewareFor('update', 'permission:financeiro.update')
            ->middlewareFor('destroy', 'permission:financeiro.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['conta-corrente-clientes' => 'contaCorrenteCliente']);
    });

    // Configurações
    Route::prefix('configuracoes')->group(function () {
        Route::get('/empresa', [EmpresaController::class, 'show'])->middleware('permission:configuracoes.read')->name('configuracoes.empresa');
        Route::post('/empresa', [EmpresaController::class, 'update'])->middleware('permission:configuracoes.update')->name('configuracoes.empresa.update');

        Route::resource('paises', PaisController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show']);
        Route::resource('funcoes', ContactoFuncaoController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show']);
        Route::resource('iva', IvaController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show']);
        Route::resource('calendario-tipos', CalendarioTipoController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['calendario-tipos' => 'tipo']);
        Route::resource('calendario-acoes', CalendarioAcaoController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show'])
            ->parameters(['calendario-acoes' => 'acao']);
        Route::resource('artigos', ArtigoController::class)
            ->middleware('permission:configuracoes.read')
            ->middlewareFor('store', 'permission:configuracoes.create')
            ->middlewareFor('update', 'permission:configuracoes.update')
            ->middlewareFor('destroy', 'permission:configuracoes.delete')
            ->except(['create', 'edit', 'show']);
        Route::get('/artigos/foto/{artigo}', [ArtigoController::class, 'foto'])
            ->middleware('permission:configuracoes.read')
            ->name('configuracoes.artigos.foto');

        Route::get('/logs', [LogsController::class, 'index'])->middleware('permission:configuracoes.read')->name('configuracoes.logs');
    });

    // Tenants
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenants/{tenant}/preferences', [TenantController::class, 'preferences'])->name('tenants.preferences');
    Route::post('/tenants/{tenant}/switch', [TenantController::class, 'switch'])->name('tenants.switch');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    
    // Onboarding endpoints for tenant self-service
    Route::post('/tenants/{tenant}/onboarding/invite', [TenantController::class, 'invite'])->name('tenants.onboarding.invite');
    Route::post('/tenants/{tenant}/onboarding/complete', [TenantController::class, 'completeOnboarding'])->name('tenants.onboarding.complete');
    
    Route::get('/onboarding', function () {
        return Inertia::render('Tenants/Onboarding');
    })->name('onboarding');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.show');
    Route::get('/', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::post('/cancelar', [BillingController::class, 'cancelar'])->name('billing.cancelar');
    Route::post('/trial', [BillingController::class, 'iniciarTrial'])->name('billing.trial');
});

require __DIR__.'/auth.php';