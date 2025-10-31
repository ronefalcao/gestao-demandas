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
        Schema::create('demanda_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demanda_id')->constrained('demandas')->onDelete('cascade');
            $table->string('nome_original');
            $table->string('nome_arquivo');
            $table->string('caminho');
            $table->string('tipo', 10)->nullable(); // pdf, jpeg, png, etc
            $table->bigInteger('tamanho')->nullable(); // em bytes (bigInteger para suportar arquivos maiores)
            $table->timestamps();

            // Índices
            $table->index('demanda_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demanda_arquivos');
    }
};
