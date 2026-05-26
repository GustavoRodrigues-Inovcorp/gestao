<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {
            $table->timestamp('trial_aviso_enviado_em')->nullable()->after('plano_pendente_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {
            $table->dropColumn('trial_aviso_enviado_em');
        });
    }
};