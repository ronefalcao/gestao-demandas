<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();

            // Índice para nome (pode ser usado em buscas)
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};