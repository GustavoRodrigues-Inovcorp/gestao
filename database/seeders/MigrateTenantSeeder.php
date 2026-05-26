<?php
namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MigrateTenantSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) return;

        // Cria tenant base
        $tenant = Tenant::firstOrCreate(
            ['owner_id' => $admin->id],
            [
                'nome'   => 'Workspace Principal',
                'slug'   => 'workspace-' . Str::random(6),
                'estado' => 'ativo',
            ]
        );

        // Associa admin ao tenant
        if (!$tenant->users()->where('user_id', $admin->id)->exists()) {
            $tenant->users()->attach($admin->id, ['role' => 'owner']);
        }

        // Associa todos os utilizadores ao tenant
        User::where('id', '!=', $admin->id)->each(function ($user) use ($tenant) {
            if (!$tenant->users()->where('user_id', $user->id)->exists()) {
                $tenant->users()->attach($user->id, ['role' => 'member']);
            }
        });

        // Atualiza todos os registos com o tenant_id
        $tables = [
            'entidades', 'contactos', 'propostas', 'encomendas',
            'encomendas_fornecedor', 'ordens_trabalho', 'faturas_fornecedor',
            'calendario_eventos', 'arquivo_digital', 'activity_logs',
            'contas_bancarias', 'conta_corrente_clientes', 'artigos',
            'paises', 'contactos_funcoes', 'iva', 'calendario_tipos',
            'calendario_acoes', 'empresa',
        ];

        foreach ($tables as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        }

        $this->command->info("Tenant criado: {$tenant->nome} (ID: {$tenant->id})");
        $this->command->info("Todos os dados associados ao tenant.");
    }
}