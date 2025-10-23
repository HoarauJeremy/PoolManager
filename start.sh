#!/usr/bin/env bash
set -euo pipefail

echo "==> start.sh :: boot"

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
PORT="${PORT:-80}"

# Bascule dev si on charge les fixtures (une seule fois)
if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
  export APP_ENV=dev
  export APP_DEBUG=1
else
  export APP_ENV=prod
  export APP_DEBUG=0
fi

echo "FLAGS => APP_ENV=${APP_ENV} RUN_BOOT_TASKS=${RUN_BOOT_TASKS:-0} LOAD_FIXTURES=${LOAD_FIXTURES:-0}"

# Healthcheck rapide
mkdir -p /var/www/html/public
echo "OK" > /var/www/html/public/healthz
chown www-data:www-data /var/www/html/public/healthz

# Apache -> bon port
if [[ -n "${PORT}" && "${PORT}" != "80" ]]; then
  sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  sed -i "s/\*:80/*:${PORT}/g" /etc/apache2/sites-available/000-default.conf || true
fi

# ServerName
if [[ ! -f /etc/apache2/conf-available/servername.conf ]]; then
  echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
  a2enconf servername >/dev/null 2>&1 || true
fi

cd /var/www/html
echo "-> Vérification des fichiers"
ls -la public/ || true
ls -la public/index.php || true

# -------- 1) Composer AVANT Apache (pour créer vendor/autoload_runtime.php) --------
if [[ -f composer.json ]]; then
  echo "-> composer install (synchrone)"
  if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
    composer install --no-interaction --optimize-autoloader
  else
    composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
  fi
fi

# Logs Symfony
mkdir -p var/log
touch var/log/prod.log
chown -R www-data:www-data var
tail -n 200 -F var/log/prod.log &

# -------- 2) Stub Encore pour éviter 500 pendant le build --------
mkdir -p public/build
if [[ ! -f public/build/entrypoints.json ]]; then
  # stub avec une entrée "app" vide
  echo '{"entrypoints":{"app":{"js":[],"css":[]}}}' > public/build/entrypoints.json
  chown www-data:www-data public/build/entrypoints.json
fi

# -------- 3) Démarrer Apache (répond au healthcheck tout de suite) --------
echo "==> start Apache (background)"
apache2-foreground &
APACHE_PID=$!

# -------- 4) Tout le reste EN ARRIÈRE-PLAN --------
(
  set -e
  echo "-> build front / migrations / cache / assets / fixtures (async)"

  # FRONT
  if [[ -f package.json ]]; then
    # pas de package-lock ? npm ci va râler -> fallback npm install
    (npm ci || npm install --legacy-peer-deps) || true
    (npm run build || npm run prod) || true
  fi

  # Permissions avant les commandes Symfony pour éviter les problèmes de cache
  chown -R www-data:www-data /var/www/html
  chmod -R 755 /var/www/html

  # BACK (exécuté en tant que www-data pour que le cache ait les bonnes permissions)
  if [[ -f bin/console ]]; then
    su -s /bin/bash www-data -c "php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=${APP_ENV}" || true
    su -s /bin/bash www-data -c "php bin/console cache:clear --no-warmup --env=${APP_ENV}" || true
    su -s /bin/bash www-data -c "php bin/console cache:warmup --env=${APP_ENV}" || true
    su -s /bin/bash www-data -c "php bin/console assets:install --no-interaction --env=${APP_ENV}" || true

    if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
      su -s /bin/bash www-data -c "php -d memory_limit=-1 bin/console doctrine:fixtures:load --no-interaction -vvv --env=${APP_ENV}" || true
    fi
  fi

  # Permissions finales pour être sûr
  chown -R www-data:www-data /var/www/html
  chmod -R 755 /var/www/html
) &

wait "$APACHE_PID"
