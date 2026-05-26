<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'entidades', 'contactos', 'propostas', 'encomendas',
            'encomendas_fornecedor', 'ordens_trabalho', 'faturas_fornecedor',
            'calendario_eventos', 'arquivo_digital', 'activity_logs',
            'contas_bancarias', 'conta_corrente_clientes', 'artigos',
            'paises', 'contactos_funcoes', 'iva', 'calendario_tipos',
            'calendario_acoes', 'empresa',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tables', function (Blueprint $table) {
            //
        });
    }
};
