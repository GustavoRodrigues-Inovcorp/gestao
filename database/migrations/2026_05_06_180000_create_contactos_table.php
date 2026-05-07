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
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->string('nome');
            $table->string('apelido')->nullable();
            $table->foreignId('funcao_id')->nullable()->constrained('contactos_funcoes')->nullOnDelete();
            $table->string('telefone')->nullable();
            $table->string('telemovel')->nullable();
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
        Schema::dropIfExists('contactos');
    }
};
