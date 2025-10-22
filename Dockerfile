# Image unique PHP 8.3 + Apache, Node, Composer, Symfony CLI, Xdebug (off par défaut)
FROM php:8.3-apache

# Dépendances système & extensions PHP
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       git unzip zip libicu-dev libzip-dev curl gnupg2 ca-certificates \
       apt-transport-https lsb-release \
  && docker-php-ext-install intl pdo_mysql zip opcache \
  && a2enmod rewrite headers expires \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash - \
  && apt-get update && apt-get install -y symfony-cli \
  && rm -rf /var/lib/apt/lists/*

# NodeJS (pour Tailwind/DaisyUI)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update && apt-get install -y nodejs \
  && npm install -g npm@latest \
  && rm -rf /var/lib/apt/lists/*

# Docroot Symfony => /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf

# Répertoire de travail
WORKDIR /var/www/html

# Copier le code source de l'application
COPY . /var/www/html/

# Copier le script de démarrage
COPY ./start.sh /usr/local/bin/start.sh

# Droits par défaut
RUN chown -R www-data:www-data /var/www/html

# Configuration Apache
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# Ports & CMD
EXPOSE 80
CMD ["/usr/local/bin/start.sh"]
