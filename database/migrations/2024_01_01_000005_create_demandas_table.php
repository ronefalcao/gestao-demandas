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
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('modulo');
            $table->text('descricao');
            $table->foreignId('status_id')->constrained('status')->onDelete('cascade');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};