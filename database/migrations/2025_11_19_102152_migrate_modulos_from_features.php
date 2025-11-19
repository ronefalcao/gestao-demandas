<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Modulo;
use App\Models\Feature;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Buscar todos os módulos únicos por projeto das features
        $modulosPorProjeto = DB::table('features')
            ->select('projeto_id', 'modulo')
            ->whereNotNull('modulo')
            ->where('modulo', '!=', '')
            ->distinct()
            ->get()
            ->groupBy('projeto_id');

        // Criar módulos na tabela modulos
        foreach ($modulosPorProjeto as $projetoId => $modulos) {
            foreach ($modulos as $moduloData) {
                $moduloNome = $moduloData->modulo;

                // Verificar se o módulo já existe
                $modulo = Modulo::where('projeto_id', $projetoId)
                    ->where('nome', $moduloNome)
                    ->first();

                if (!$modulo) {
                    // Criar novo módulo
                    $modulo = Modulo::create([
                        'projeto_id' => $projetoId,
                        'nome' => $moduloNome,
                        'descricao' => null,
                    ]);
                }

                // Atualizar todas as features com este módulo
                DB::table('features')
                    ->where('projeto_id', $projetoId)
                    ->where('modulo', $moduloNome)
                    ->update(['modulo_id' => $modulo->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: atualizar features com o nome do módulo
        $features = Feature::whereNotNull('modulo_id')->with('modulo')->get();

        foreach ($features as $feature) {
            if ($feature->modulo) {
                DB::table('features')
                    ->where('id', $feature->id)
                    ->update(['modulo' => $feature->modulo->nome]);
            }
        }
    }
};
