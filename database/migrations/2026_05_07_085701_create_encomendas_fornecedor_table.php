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
        Schema::create('encomendas_fornecedor', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->date('data')->nullable();
            $table->foreignId('fornecedor_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_id')->nullable()->constrained('encomendas')->nullOnDelete();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->enum('estado', ['rascunho', 'fechado'])->default('rascunho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomendas_fornecedor');
    }
};
