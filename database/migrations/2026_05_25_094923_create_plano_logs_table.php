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
        Schema::create('plano_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('acao'); // upgrade, downgrade, cancelamento, reativacao, trial_inicio, trial_fim
            $table->foreignId('plano_anterior_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->foreignId('plano_novo_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_logs');
    }
};
