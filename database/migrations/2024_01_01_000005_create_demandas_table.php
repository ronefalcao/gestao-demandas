<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 5)->unique(); // Número de 5 dígitos (00001, 00002, etc)
            $table->date('data');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->onDelete('restrict');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('modulo');
            $table->text('descricao');
            $table->foreignId('status_id')->constrained('status')->onDelete('restrict');
            $table->text('observacao')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index('numero');
            $table->index('data');
            $table->index('status_id');
            $table->index('projeto_id');
            $table->index('solicitante_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};
