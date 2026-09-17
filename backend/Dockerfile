FROM php:8.5-fpm-alpine

# Instala dependências do sistema via Alpine APK (Nginx, Git, Unzip)
RUN apk add --no-cache \
    nginx \
    git \
    unzip \
    bash

# Baixa o script oficial para instalar extensões PHP com facilidade
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Instala as extensões PHP (ele resolve as dependências do uuid e pdo_pgsql automaticamente)
RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    uuid

# Copia o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia a configuração do Nginx
COPY configs/nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/backend

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
