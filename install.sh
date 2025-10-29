#!/bin/bash

echo "=========================================="
echo "  Sistema de Gestão de Demandas"
echo "  Instalação Automática"
echo "=========================================="
echo ""

# Verificar se o Composer está instalado
if ! command -v composer &> /dev/null
then
    echo "❌ Composer não encontrado. Por favor, instale o Composer primeiro."
    exit 1
fi

echo "📦 Instalando dependências do Composer..."
composer install

echo ""
echo "🔐 Verificando arquivo .env..."
if [ ! -f .env ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
    echo "⚠️  IMPORTANTE: Configure as credenciais do PostgreSQL no arquivo .env"
fi

echo ""
echo "🔑 Gerando chave da aplicação..."
php artisan key:generate

echo ""
echo "=========================================="
echo "✅ Instalação concluída!"
echo ""
echo "Próximos passos:"
echo "1. Configure o arquivo .env com suas credenciais do PostgreSQL"
echo "2. Crie o banco de dados no PostgreSQL:"
echo "   CREATE DATABASE demandas;"
echo "3. Execute: php artisan migrate --seed"
echo "4. Inicie o servidor: php artisan serve"
echo ""
echo "Credenciais padrão:"
echo "  Email: admin@demandas.com"
echo "  Senha: admin123"
echo "=========================================="




