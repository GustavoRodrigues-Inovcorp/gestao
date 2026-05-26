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
        Schema::create('subscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos');
            $table->enum('estado', ['trial', 'ativa', 'cancelada', 'expirada', 'pendente'])->default('trial');
            $table->enum('ciclo', ['mensal', 'anual'])->default('mensal');
            $table->decimal('preco', 10, 2)->default(0);
            $table->timestamp('trial_inicio')->nullable();
            $table->timestamp('trial_fim')->nullable();
            $table->timestamp('inicio')->nullable();
            $table->timestamp('fim')->nullable();
            $table->timestamp('proximo_ciclo')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->string('cancelamento_motivo')->nullable();
            $table->foreignId('plano_pendente_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscricoes');
    }
};
