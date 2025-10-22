#!/usr/bin/env bash
set -euo pipefail

echo "==> start.sh :: boot"

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
PORT="${PORT:-80}"

# Forcer le mode production (même si Railway impose dev)
export APP_ENV=prod
export APP_DEBUG=0

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

# 3) Installation des dépendances (obligatoire)
if [[ -f composer.json ]]; then
  echo "-> composer install (no-dev)"
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

# 5) Maintenance Symfony (migrations, cache, assets) — optionnel
if [[ -f bin/console ]]; then
  echo "-> Exécution des tâches Symfony essentielles"
  if php bin/console list doctrine:migrations:migrate >/dev/null 2>&1; then
    echo "-> doctrine:migrations:migrate"
    php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod || true
  else
    echo "-> doctrine:schema:update --force (fallback)"
    php bin/console doctrine:schema:update --force --no-interaction --env=prod || true
  fi

  echo "-> cache:clear & warmup"
  php bin/console cache:clear --no-warmup --env=prod || true
  php bin/console cache:warmup --env=prod || true

  echo "-> assets:install"
  php bin/console assets:install --no-interaction --env=prod || true

  # Charger les fixtures seulement si RUN_BOOT_TASKS=1
  if [[ "${RUN_BOOT_TASKS:-0}" == "1" ]]; then
    echo "-> doctrine:fixtures:load (première fois uniquement)"
    php bin/console doctrine:fixtures:load --no-interaction --env=prod || true
  else
    echo "-> Fixtures ignorées (RUN_BOOT_TASKS=0)"
  fi
fi

# 6) Permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "==> start Apache"
exec apache2-foreground
