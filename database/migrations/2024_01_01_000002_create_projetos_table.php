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
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Índice para nome (pode ser usado em buscas)
            $table->index('nome');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projetos');
    }
};
