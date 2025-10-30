FROM php:8.2-fpm

# 🛠️ Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libpq-dev \
    libzip-dev \
 && docker-php-ext-configure zip \
 && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath

# 🎼 Copia o Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . .

# Instala dependências do PHP
RUN composer install --optimize-autoloader --no-dev

# Permissões
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

# Expõe a porta
EXPOSE 8000

# Comando para rodar a aplicação
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
