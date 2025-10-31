<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('cor', 7)->nullable(); // Código hexadecimal da cor (#RRGGBB)
            $table->integer('ordem')->default(0);
            $table->timestamps();

            // Índices
            $table->index('nome');
            $table->index('ordem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};



