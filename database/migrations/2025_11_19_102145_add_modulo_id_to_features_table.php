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
        Schema::table('features', function (Blueprint $table) {
            $table->foreignId('modulo_id')->nullable()->after('projeto_id')->constrained('modulos')->onDelete('set null');
            $table->index('modulo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropForeign(['modulo_id']);
            $table->dropIndex(['modulo_id']);
            $table->dropColumn('modulo_id');
        });
    }
};
