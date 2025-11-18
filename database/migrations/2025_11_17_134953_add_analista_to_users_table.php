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
        // O Laravel cria uma constraint de check para o enum
        // Precisamos remover a constraint antiga e criar uma nova com 'analista' incluído

        // Remove a constraint antiga se existir
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_check");

        // Cria uma nova constraint com 'analista' incluído
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_check CHECK (tipo::text = ANY (ARRAY['administrador'::character varying, 'gestor'::character varying, 'analista'::character varying, 'usuario'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a constraint com 'analista'
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_check");

        // Restaura a constraint original sem 'analista'
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_check CHECK (tipo::text = ANY (ARRAY['administrador'::character varying, 'gestor'::character varying, 'usuario'::character varying]::text[]))");
    }
};
