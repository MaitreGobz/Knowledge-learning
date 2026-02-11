FROM php:8.4-apache

# Packages système + extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpq-dev default-mysql-client \
    && docker-php-ext-install intl zip pdo pdo_mysql pdo_pgsql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le code
COPY . .

# Installer dépendances prod
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Cache Symfony prod
RUN php bin/console cache:clear --env=prod \
    && php bin/console cache:warmup --env=prod \
    && chown -R www-data:www-data var

# Apache: document root -> public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Démarrage: migrations puis apache
CMD bash -lc "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true; apache2-foreground"
