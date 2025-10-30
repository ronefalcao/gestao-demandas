# Instruções para Executar o Projeto com Docker

Este documento contém as instruções para executar o sistema de gestão de demandas usando Docker.

## Pré-requisitos

- Docker instalado
- Docker Compose instalado

## Configuração

O projeto está configurado para rodar na porta **8003**.

### Serviços Disponíveis

- **Aplicação Laravel**: http://localhost:8003
- **phpMyAdmin**: http://localhost:8080
- **MySQL**: porta 3307

## Como Executar

### 1. Inicie os containers

```bash
docker-compose up -d --build
```

### 2. Configure o ambiente

```bash
docker-compose exec app cp .env.example .env
```

### 3. Instale as dependências

```bash
docker-compose exec app composer install
```

### 4. Configure a chave da aplicação

```bash
docker-compose exec app php artisan key:generate
```

### 5. Execute as migrações

```bash
docker-compose exec app php artisan migrate --seed
```

### 6. Configure as permissões

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## Acessar a Aplicação

Abra seu navegador e acesse: **http://localhost:8003**

## Comandos Úteis

### Parar os containers

```bash
docker-compose down
```

### Ver logs

```bash
docker-compose logs -f
```

### Entrar no container da aplicação

```bash
docker-compose exec app bash
```

### Executar comandos artisan

```bash
docker-compose exec app php artisan [comando]
```

### Ver containers em execução

```bash
docker-compose ps
```

## Credenciais do Banco de Dados

- **Host**: mysql
- **Porta**: 3306 (dentro do container) / 3307 (na sua máquina)
- **Database**: laravel
- **Usuário**: laravel_user
- **Senha**: laravel_password
- **Root Password**: root_password

## phpMyAdmin

Para acessar o phpMyAdmin:

- URL: http://localhost:8080
- Usuário: laravel_user
- Senha: laravel_password

## Troubleshooting

### Limpar tudo e reiniciar

```bash
docker-compose down -v
docker-compose up -d --build
```

### Ver erros do nginx

```bash
docker-compose logs nginx
```

### Ver erros do PHP

```bash
docker-compose logs app
```

