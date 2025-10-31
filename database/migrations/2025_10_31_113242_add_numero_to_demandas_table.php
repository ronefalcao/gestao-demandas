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
        Schema::table('demandas', function (Blueprint $table) {
            $table->string('numero')->nullable()->after('id');
        });

        // Popular demandas existentes com números
        $ano = date('Y');
        $demandas = \App\Models\Demanda::whereNull('numero')->orderBy('created_at')->get();
        $sequencial = 1;

        foreach ($demandas as $demanda) {
            $demanda->numero = 'DEM-' . $ano . '-' . str_pad($sequencial, 4, '0', STR_PAD_LEFT);
            $demanda->save();
            $sequencial++;
        }

        // Tornar o campo único e obrigatório
        Schema::table('demandas', function (Blueprint $table) {
            $table->string('numero')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->dropColumn('numero');
        });
    }
};
