#!/bin/bash
set -e

# Wait for database with timeout
echo "Waiting for database..."
max_attempts=30
attempt=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ $attempt -ge $max_attempts ]; then
    echo "Error: Database connection timeout after $max_attempts attempts"
    echo "Please check your DATABASE_URL environment variable"
    exit 1
  fi
  echo "Database is unavailable - sleeping (attempt $attempt/$max_attempts)"
  sleep 2
done

echo "Database is up - executing migrations and fixtures"

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Load fixtures only if LOAD_FIXTURES environment variable is set to true
if [ "$LOAD_FIXTURES" = "true" ]; then
  echo "Loading fixtures..."
  php bin/console doctrine:fixtures:load --no-interaction
  echo "Fixtures loaded successfully!"
else
  echo "Skipping fixtures (set LOAD_FIXTURES=true to enable)"
fi

# Clear cache
php bin/console cache:clear

# Configure Apache to listen on PORT environment variable (for Render.com and other cloud platforms)
if [ -n "$PORT" ]; then
  echo "Configuring Apache to listen on port $PORT"
  sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
  sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

npm run build

php bin/console cache:clear

# Start Apache
exec apache2-foreground
