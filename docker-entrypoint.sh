#!/bin/bash
set -e

# Attendre que la base de données soit prête
echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

echo "Database is up - executing migrations and fixtures"

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Charger les fixtures
php bin/console doctrine:fixtures:load --no-interaction

echo "Fixtures loaded successfully!"

npm run build

php bin/console cache:clear

# Démarrer Apache
exec apache2-foreground
