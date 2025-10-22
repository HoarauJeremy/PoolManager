#!/usr/bin/env bash
set -euo pipefail

echo "==> start.sh (in-container) :: boot sequence"

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
PORT="${PORT:-80}"

# 1) Adapter Apache au port imposé par la plateforme (Railway fournit $PORT)
if [[ -n "${PORT}" && "${PORT}" != "80" ]]; then
  echo "-> Apache listen on ${PORT}"
  sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  sed -i "s/\*:80/*:${PORT}/g" /etc/apache2/sites-available/000-default.conf || true
fi

# 2) Supprimer le warning ServerName
if [[ ! -f /etc/apache2/conf-available/servername.conf ]]; then
  echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
  a2enconf servername >/dev/null 2>&1 || true
fi

cd /var/www/html

# 3) Attente DB si DATABASE_URL fourni (MySQL/Postgres)
DB_URL="${DATABASE_URL:-}"
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-}"
if [[ -z "${DB_HOST}" || -z "${DB_PORT}" ]]; then
  if [[ -n "${DB_URL}" ]]; then
    DB_HOST=$(echo "$DB_URL" | sed -E 's#.*@([^:/?]+).*#\1#')
    DB_PORT=$(echo "$DB_URL" | sed -E 's#.*:([0-9]+).*/.*#\1#')
  fi
fi
DB_PORT="${DB_PORT:-3306}"

if [[ -n "${DB_HOST}" ]]; then
  echo "-> Waiting DB ${DB_HOST}:${DB_PORT} ..."
  for i in {1..60}; do
    if timeout 1 bash -c "cat </dev/null >/dev/tcp/${DB_HOST}/${DB_PORT}" 2>/dev/null; then
      echo "   DB is up."
      break
    fi
    echo "   retry $i"
    sleep 1
  done
fi

# 4) Dépendances (fallback si vendor/node_modules non présents)
if [[ -f composer.json && ! -d vendor ]]; then
  echo "-> composer install (no-dev)"
  composer install --no-interaction --optimize-autoloader
fi

if [[ -f package.json && ! -d node_modules ]]; then
  echo "-> npm install (optional)"
  (npm ci || npm install --legacy-peer-deps) || true
fi

# 5) Build front (si applicable)
if [[ -f package.json ]]; then
  echo "-> npm run build (optional)"
  (npm run build || npm run prod) || true
fi

# 6) Maintenance Symfony
if [[ -f bin/console ]]; then
  if php bin/console list doctrine:migrations:migrate >/dev/null 2>&1; then
    echo "-> doctrine:migrations:migrate"
    php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
  else
    echo "-> doctrine:schema:update --force (fallback)"
    php bin/console doctrine:schema:update --force --no-interaction
  fi

  echo "-> cache:clear & warmup"
  php bin/console cache:clear --no-warmup
  php bin/console cache:warmup

  echo "-> assets:install"
  php bin/console assets:install --no-interaction || true
fi

# 7) Healthcheck: on ne déclare OK qu'à la toute fin
mkdir -p public
echo "OK" > public/healthz

echo "==> start Apache"
exec apache2-foreground
