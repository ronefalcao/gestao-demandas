<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Projeto;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // Status
        Status::create(['nome' => 'Solicitada',       'cor' => '#f0ad4e', 'ordem' => 1]); // Laranja suave → pendente
        Status::create(['nome' => 'Em Análise',       'cor' => '#17a2b8', 'ordem' => 2]); // Azul claro → análise técnica
        Status::create(['nome' => 'Backlog',          'cor' => '#6c757d', 'ordem' => 3]); // Cinza → aguardando prioridade
        Status::create(['nome' => 'Em Desenvolvimento', 'cor' => '#007bff', 'ordem' => 4]); // Azul → progresso ativo
        Status::create(['nome' => 'Em Teste',         'cor' => '#6610f2', 'ordem' => 5]); // Roxo → validação
        Status::create(['nome' => 'Concluído',        'cor' => '#28a745', 'ordem' => 6]); // Verde → finalizado com sucesso
        Status::create(['nome' => 'Homologada',       'cor' => '#20c997', 'ordem' => 7]); // Verde água → aprovado
        Status::create(['nome' => 'Publicada',        'cor' => '#0dcaf0', 'ordem' => 8]); // Azul piscina → disponível
        Status::create(['nome' => 'Cancelada',        'cor' => '#dc3545', 'ordem' => 9]); // Vermelho → encerrado


        // Usuário Administrador
        User::create([
            'nome' => 'Administrador',
            'email' => 'admin@demandas.com',
            'telefone' => '11999999999',
            'password' => Hash::make('admin123'),
            'tipo' => 'administrador',
        ]);

        // Usuário Gestor
        User::create([
            'nome' => 'Gestor',
            'email' => 'gestor@demandas.com',
            'telefone' => '11999999999',
            'password' => Hash::make('gestor123'),
            'tipo' => 'gestor',
        ]);

        // Usuário Comum
        User::create([
            'nome' => 'Usuário Teste',
            'email' => 'usuario@demandas.com',
            'telefone' => '11999999999',
            'password' => Hash::make('usuario123'),
            'tipo' => 'usuario',
        ]);


        // Clientes
        $clientes = [
            ['nome' => 'Prefeitura de Juazeiro do Norte'],
            ['nome' => 'Prefeitura de Acarape'],
            ['nome' => 'Prefeitura de Horizonte'],
            ['nome' => 'Prefeitura de Jaguaribe'],
            ['nome' => 'Prefeitura de Limoeiro do Norte'],
            ['nome' => 'Prefeitura de Pacajus'],
            ['nome' => 'Prefeitura de Pacatuba'],
            ['nome' => 'Prefeitura de Nova Jaguaribara'],
            ['nome' => 'Prefeitura de Sobral'],
            ['nome' => 'Prefeitura de Tabuleiro do Norte'],
            ['nome' => 'Prefeitura de Potiretama'],
            ['nome' => 'Prefeitura de Novo Oriente'],
            ['nome' => 'Prefeitura de Viçosa do Ceará'],
        ];
        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        // Projetos
        $projetos = [
            ['nome' => 'Integrado Antigo'],
            ['nome' => 'Integrado 2.0'],
            ['nome' => 'Sistema de Gestão Educação'],
            ['nome' => 'Transporte Universitário'],
        ];
        foreach ($projetos as $projeto) {
            Projeto::create($projeto);
        }
    }
}