#!/usr/bin/env bash
set -euo pipefail

echo "==> start.sh :: boot"

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
PORT="${PORT:-80}"

# Choisir l'env selon LOAD_FIXTURES (one-shot)
if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
  export APP_ENV=dev
  export APP_DEBUG=1
else
  export APP_ENV=prod
  export APP_DEBUG=0
fi

echo "FLAGS => APP_ENV=${APP_ENV} RUN_BOOT_TASKS=${RUN_BOOT_TASKS:-0} LOAD_FIXTURES=${LOAD_FIXTURES:-0}"
php -r 'echo "PHP sees APP_ENV=".getenv("APP_ENV").PHP_EOL;' || true

# --- Healthcheck immédiat (pour que Railway voie vite "OK") ---
mkdir -p /var/www/html/public
echo "OK" > /var/www/html/public/healthz
chown www-data:www-data /var/www/html/public/healthz

# 1) Adapter Apache au port de Railway ($PORT)
if [[ -n "${PORT}" && "${PORT}" != "80" ]]; then
  echo "-> Apache listen on ${PORT}"
  sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  sed -i "s/\*:80/*:${PORT}/g" /etc/apache2/sites-available/000-default.conf || true
fi

# 2) Eviter le warning ServerName
if [[ ! -f /etc/apache2/conf-available/servername.conf ]]; then
  echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
  a2enconf servername >/dev/null 2>&1 || true
fi

cd /var/www/html

# Debug: Vérifier que les fichiers sont présents
echo "-> Vérification des fichiers"
ls -la public/ || echo "Dossier public non trouvé"
ls -la public/index.php || echo "index.php non trouvé"

# 3) Streaming des logs Symfony vers Railway
echo "-> Configuration du streaming des logs Symfony"
mkdir -p /var/www/html/var/log
touch /var/www/html/var/log/prod.log
chown www-data:www-data /var/www/html/var/log/prod.log
tail -n 200 -F /var/www/html/var/log/prod.log &

# --- Eviter le 500 si le build Webpack n'a pas encore fini ---
mkdir -p /var/www/html/public/build
if [[ ! -f /var/www/html/public/build/entrypoints.json ]]; then
  echo '{"entrypoints":{}}' > /var/www/html/public/build/entrypoints.json
  chown www-data:www-data /var/www/html/public/build/entrypoints.json
fi

echo "==> start Apache (background)"
apache2-foreground &         # Apache démarre pour répondre au healthcheck
APACHE_PID=$!

(
  set -e
  echo "-> Composer / build / migrations / cache / fixtures en arrière-plan"

  # --- FRONT ---
  if [[ -f package.json ]]; then
    (npm ci || npm install --legacy-peer-deps) || true
    (npm run build || npm run prod) || true   # doit générer public/build/** et entrypoints.json
  fi

  # --- BACK ---
  if [[ -f composer.json ]]; then
    if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
      composer install --no-interaction --optimize-autoloader
    else
      composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
    fi
  fi

  if [[ -f bin/console ]]; then
    php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env="${APP_ENV}" || true
    php bin/console cache:clear --no-warmup --env="${APP_ENV}" || true
    php bin/console cache:warmup --env="${APP_ENV}" || true
    php bin/console assets:install --no-interaction --env="${APP_ENV}" || true

    if [[ "${LOAD_FIXTURES:-0}" == "1" ]]; then
      php -d memory_limit=-1 bin/console doctrine:fixtures:load --no-interaction -vvv --env="${APP_ENV}" || true
    fi
  fi

  chown -R www-data:www-data /var/www/html
  chmod -R 755 /var/www/html
) &

# Attendre Apache
wait "$APACHE_PID"
