# Sistema de Gestão de Demandas

Sistema completo de gestão de demandas desenvolvido em Laravel 10 com arquitetura MVC, utilizando PostgreSQL como banco de dados.

## Características

- ✅ Autenticação de usuários
- ✅ CRUD completo de Clientes
- ✅ CRUD completo de Status
- ✅ CRUD completo de Demandas
- ✅ Dashboard com totais por status
- ✅ Relatórios em PDF
- ✅ Interface moderna e responsiva com Bootstrap 5
- ✅ Arquitetura MVC
- ✅ Banco de dados PostgreSQL

## Requisitos

- PHP 8.1 ou superior
- Composer
- PostgreSQL 12 ou superior
- Node.js e NPM (opcional, para assets)

## Instalação

1. Clone ou baixe o repositório
2. Instale as dependências:

```bash
composer install
```

3. Configure o arquivo `.env` com suas credenciais do PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=demandas
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

4. Gere a chave da aplicação:

```bash
php artisan key:generate
```

5. Execute as migrations e seeders:

```bash
php artisan migrate --seed
```

## Credenciais de Acesso

Após executar o seeder, você pode fazer login com:

- **Email:** admin@demandas.com
- **Senha:** admin123

## Estrutura do Banco de Dados

### Tabelas

- **users**: Usuários do sistema (nome, email, telefone, password)
- **cidades**: Cidades dos clientes (nome, estado)
- **clientes**: Clientes (nome)
- **status**: Status das demandas (id, nome, cor, ordem)
- **demandas**: Demandas (data, cliente_id, user_id, modulo, descricao, status_id, observacao)

## Funcionalidades

### Clientes

- Listar, criar, editar, visualizar e excluir clientes
- Filtro por cidade

### Status

- Gerenciamento de status com cores personalizadas
- Ordenação por prioridade

### Demandas

- CRUD completo de demandas
- Filtros por cliente e status
- Relatório em PDF
- Dashboard com totais por status

### Dashboard

- Visão geral de totais de demandas por status
- Últimas demandas criadas

## Relatórios

O sistema permite exportar demandas filtradas em formato PDF através da página de listagem de demandas.

## Tecnologias Utilizadas

- Laravel 10
- PostgreSQL
- Bootstrap 5
- Bootstrap Icons
- dompdf (para geração de PDFs)

## Estrutura de Pastas

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── ClienteController.php
│   │   ├── DashboardController.php
│   │   ├── DemandaController.php
│   │   └── StatusController.php
│   └── Middleware/
├── Models/
├── User.php
├── Cidade.php
├── Cliente.php
├── Status.php
└── Demanda.php
database/
├── migrations/
└── seeders/
resources/
└── views/
    ├── layouts/
    ├── auth/
    ├── clientes/
    ├── demandas/
    ├── status/
    └── dashboard/
routes/
└── web.php
```

## Licença

MIT
