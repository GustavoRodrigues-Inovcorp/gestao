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
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->decimal('preco_mensal', 10, 2)->default(0);
            $table->decimal('preco_anual', 10, 2)->default(0);
            $table->integer('max_utilizadores')->default(1);
            $table->integer('max_clientes')->default(50);
            $table->integer('max_documentos')->default(100);
            $table->integer('trial_dias')->default(14);
            $table->boolean('ativo')->default(true);
            $table->boolean('is_free')->default(false);
            $table->json('features')->nullable(); // funcionalidades incluídas
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};
