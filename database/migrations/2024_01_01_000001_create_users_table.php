<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('telefone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('tipo', ['administrador', 'gestor', 'usuario'])->default('usuario');
            $table->rememberToken();
            $table->timestamps();

            // Índices
            $table->index('tipo');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};