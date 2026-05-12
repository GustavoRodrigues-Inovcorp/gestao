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
        Schema::create('ordem_trabalho_linhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_trabalho_id')->constrained('ordens_trabalho')->cascadeOnDelete();
            $table->foreignId('artigo_id')->nullable()->constrained('artigos')->nullOnDelete();
            $table->string('descricao');
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->string('unidade')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_trabalho_linhas');
    }
};
