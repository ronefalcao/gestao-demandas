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
        // Converter números antigos (DEM-YYYY-NNNN) para sequencial de 5 dígitos
        $demandas = \App\Models\Demanda::all();
        $sequencial = 1;

        foreach ($demandas as $demanda) {
            $demanda->numero = str_pad($sequencial, 5, '0', STR_PAD_LEFT);
            $demanda->save();
            $sequencial++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voltar ao formato antigo DEM-2025-NNNN
        $ano = date('Y');
        $demandas = \App\Models\Demanda::orderByRaw('CAST(numero AS UNSIGNED)')->get();
        $sequencial = 1;

        foreach ($demandas as $demanda) {
            $demanda->numero = 'DEM-' . $ano . '-' . str_pad($sequencial, 4, '0', STR_PAD_LEFT);
            $demanda->save();
            $sequencial++;
        }
    }
};
