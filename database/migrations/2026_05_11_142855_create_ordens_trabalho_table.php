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
        Schema::create('ordens_trabalho', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->date('data');
            $table->foreignId('entidade_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('contacto_id')->nullable()->constrained('contactos')->nullOnDelete();
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', ['aberta', 'em_progresso', 'concluida', 'cancelada'])->default('aberta');
            $table->date('data_prevista')->nullable();
            $table->date('data_conclusao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_trabalho');
    }
};
