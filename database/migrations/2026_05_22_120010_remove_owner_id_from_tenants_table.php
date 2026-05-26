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
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'owner_id')) {
                // Attempt to drop foreign key if it exists, ignore errors
                try {
                    $table->dropForeign(['owner_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key doesn't exist or name differs
                }

                // Drop the column
                try {
                    $table->dropColumn('owner_id');
                } catch (\Exception $e) {
                    // ignore if column was already removed by other means
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'owner_id')) {
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            }
        });
    }
};
