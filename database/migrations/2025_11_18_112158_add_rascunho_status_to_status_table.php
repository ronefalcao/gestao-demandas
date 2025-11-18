<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar status "Rascunho" caso não exista
        $rascunhoExists = DB::table('status')->where('nome', 'Rascunho')->exists();

        if (!$rascunhoExists) {
            // Verificar se já existe algum status para determinar a ordem mínima
            $minOrdem = DB::table('status')->min('ordem') ?? 0;

            DB::table('status')->insert([
                'nome' => 'Rascunho',
                'cor' => '#6c757d',
                'ordem' => $minOrdem - 1, // Colocar antes de todos os outros
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover status "Rascunho" se existir
        DB::table('status')->where('nome', 'Rascunho')->delete();
    }
};