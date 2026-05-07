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
        Schema::create('encomenda_fornecedor_linhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_fornecedor_id')->constrained('encomendas_fornecedor')->cascadeOnDelete();
            $table->foreignId('artigo_id')->nullable()->constrained('artigos')->nullOnDelete();
            $table->string('referencia')->nullable();
            $table->string('nome');
            $table->integer('quantidade')->default(1);
            $table->decimal('preco_custo', 10, 2)->default(0);
            $table->decimal('iva', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomenda_fornecedor_linhas');
    }
};
