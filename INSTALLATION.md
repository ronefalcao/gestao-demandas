# Guia de Instalação - Sistema de Gestão de Demandas

## Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- PHP 8.1 ou superior
- Composer (gerenciador de dependências do PHP)
- PostgreSQL 12 ou superior
- Extensões PHP necessárias: pgsql, mbstring, xml, openssl, pdo, bcmath

## Passo a Passo

### 1. Instalar Dependências

```bash
composer install
```

### 2. Configurar o Banco de Dados

Crie um arquivo `.env` na raiz do projeto com as seguintes configurações:

```env
APP_NAME="Sistema de Gestão de Demandas"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=demandas
DB_USERNAME=postgres
DB_PASSWORD=sua_senha_aqui
```

**Importante:** Substitua `sua_senha_aqui` pela sua senha do PostgreSQL.

### 3. Criar Banco de Dados

Conecte-se ao PostgreSQL e crie o banco de dados:

```bash
psql -U postgres
CREATE DATABASE demandas;
\q
```

### 4. Gerar Chave da Aplicação

```bash
php artisan key:generate
```

### 5. Executar Migrations e Seeders

```bash
php artisan migrate --seed
```

Isso vai:

- Criar todas as tabelas no banco de dados
- Inserir dados iniciais (cidades, status, usuário administrador)

### 6. Configurar Permissões de Diretórios

```bash
chmod -R 775 storage bootstrap/cache
```

### 7. Iniciar o Servidor de Desenvolvimento

```bash
php artisan serve
```

O sistema estará disponível em: http://localhost:8000

## Credenciais de Acesso

Após a instalação, você pode fazer login com:

- **Email:** admin@demandas.com
- **Senha:** admin123

**Importante:** Altere a senha após o primeiro acesso!

## Estrutura de Diretórios

```
demandas/
├── app/                    # Código da aplicação (MVC)
│   ├── Http/
│   │   └── Controllers/   # Controllers
│   └── Models/            # Models
├── database/
│   ├── migrations/        # Migrações do banco
│   └── seeders/           # Seeders de dados
├── resources/
│   └── views/             # Views Blade
├── routes/
│   └── web.php            # Rotas
├── config/                 # Arquivos de configuração
└── public/                 # Ponto de entrada da aplicação
```

## Solução de Problemas

### Erro de Conexão com PostgreSQL

Verifique se:

- O PostgreSQL está rodando
- As credenciais no `.env` estão corretas
- O banco de dados foi criado

### Erro "Class not found"

Execute novamente:

```bash
composer dump-autoload
```

### Erro de Permissão

Execute:

```bash
chmod -R 775 storage bootstrap/cache
```

## Próximos Passos

Após a instalação:

1. Acesse o sistema em http://localhost:8000
2. Faça login com as credenciais fornecidas
3. Explore as funcionalidades:
   - Gerenciar Clientes
   - Gerenciar Status
   - Criar Demandas
   - Visualizar Dashboard
   - Exportar Relatórios em PDF



