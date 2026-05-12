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
        Schema::create('conta_corrente_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->date('data');
            $table->string('descricao');
            $table->decimal('debito', 10, 2)->default(0);
            $table->decimal('credito', 10, 2)->default(0);
            $table->decimal('saldo', 10, 2)->default(0);
            $table->enum('tipo', ['fatura', 'pagamento', 'nota_credito', 'outro'])->default('outro');
            $table->string('referencia')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_corrente_clientes');
    }
};
