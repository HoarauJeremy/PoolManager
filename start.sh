#!/usr/bin/env bash
set -euo pipefail

echo "==> start.sh :: boot"

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
PORT="${PORT:-80}"

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

# 3) Installation des dépendances (obligatoire)
if [[ -f composer.json ]]; then
  echo "-> composer install (no-dev)"
  # S'assurer que APP_ENV est bien défini pour la production
  export APP_ENV="${APP_ENV:-prod}"
  composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
fi

if [[ -f package.json && ! -d node_modules ]]; then
  echo "-> npm install (optional)"
  (npm ci || npm install --legacy-peer-deps) || true
fi

# 4) (Optionnel) build front si présent
if [[ -f package.json ]]; then
  echo "-> npm run build (optional)"
  (npm run build || npm run prod) || true
fi

# 5) Maintenance Symfony (migrations, cache, assets)
if [[ "${RUN_BOOT_TASKS:-0}" == "1" && -f bin/console ]]; then
  if php bin/console list doctrine:migrations:migrate >/dev/null 2>&1; then
    echo "-> doctrine:migrations:migrate"
    php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod
  else
    echo "-> doctrine:schema:update --force (fallback)"
    php bin/console doctrine:schema:update --force --no-interaction --env=prod
  fi

  echo "-> cache:clear & warmup"
  php bin/console cache:clear --no-warmup --env=prod
  php bin/console cache:warmup --env=prod

  echo "-> assets:install"
  php bin/console assets:install --no-interaction --env=prod || true
fi

# 6) Vérifier les permissions et créer healthcheck
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
mkdir -p public
echo "OK" > public/healthz
chown www-data:www-data public/healthz

echo "==> start Apache"
exec apache2-foreground
