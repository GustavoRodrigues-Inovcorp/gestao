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
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->boolean('is_cliente')->default(false);
            $table->boolean('is_fornecedor')->default(false);
            $table->string('nif')->nullable();
            $table->string('nome');
            $table->string('morada')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('localidade')->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('paises')->nullOnDelete();
            $table->string('telefone')->nullable();
            $table->string('telemovel')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->boolean('rgpd')->default(false);
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
