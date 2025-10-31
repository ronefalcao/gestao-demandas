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
            $table->date('data');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('solicitante_id')->constrained('users');
            $table->foreignId('responsavel_id')->nullable()->constrained('users');
            $table->string('modulo');
            $table->text('descricao');
            $table->foreignId('status_id')->constrained('status');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};