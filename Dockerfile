# ================================
# STAGE 1: Build Node.js assets
# ================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copier les fichiers de dépendances Node.js
COPY package*.json ./

# Copier les fichiers de configuration
COPY webpack.config.js ./
COPY postcss.config.mjs ./

# Installer les dépendances Node.js (inclut dev dependencies pour le build)
# Utilise npm install au lieu de npm ci pour compatibilité avec environnements de build cloud
RUN npm install --legacy-peer-deps

# Copier les fichiers nécessaires pour le build
COPY assets ./assets
COPY public ./public

# Build des assets (Webpack Encore)
RUN npm run build

# ================================
# STAGE 2: Install PHP dependencies
# ================================
FROM composer:2 AS composer-builder

WORKDIR /app

# Copier les fichiers de dépendances Composer
COPY composer.json composer.lock symfony.lock ./

# Installer les dépendances PHP (production uniquement, optimisé)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# Copier le reste du code pour exécuter les scripts post-install
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# ================================
# STAGE 3: Production image
# ================================
FROM php:8.3-apache

# Dépendances système & extensions PHP
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       git unzip zip libicu-dev libzip-dev curl gnupg2 ca-certificates \
  && docker-php-ext-install intl pdo_mysql zip opcache \
  && a2enmod rewrite headers expires \
  && rm -rf /var/lib/apt/lists/*

# Installer Node.js 20.x dans l'image de production
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update && apt-get install -y nodejs \
  && npm install -g npm@latest \
  && rm -rf /var/lib/apt/lists/*

# Configuration PHP pour production
RUN { \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN { \
        echo 'memory_limit=512M'; \
        echo 'max_execution_time=30'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
    } > /usr/local/etc/php/conf.d/php-custom.ini

# Docroot Symfony => /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf

# Configuration Apache
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copier le code source de l'application
COPY --chown=www-data:www-data . .

# Copier les dépendances Composer depuis le builder
COPY --from=composer-builder --chown=www-data:www-data /app/vendor ./vendor

# Copier les node_modules et assets buildés depuis le builder Node
COPY --from=node-builder --chown=www-data:www-data /app/node_modules ./node_modules
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Variables d'environnement pour production
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p var/cache var/log var/sessions public/build \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 var \
    && chmod -R 755 public

# Warm up Symfony cache (si possible sans base de données)
RUN php bin/console cache:warmup --env=prod || true

# S'assurer que les permissions sont correctes après le cache warmup
RUN chown -R www-data:www-data var

# Ports & CMD Apache
EXPOSE 80
CMD ["apache2-foreground"]
