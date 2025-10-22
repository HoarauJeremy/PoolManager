#!/bin/bash
set -e

# Configure Apache to listen on PORT environment variable (for Render.com and other cloud platforms)
if [ -n "$PORT" ]; then
  echo "Configuring Apache to listen on port $PORT"
  sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
  sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

# Start Apache in background
echo "Starting Apache in background..."
apache2ctl start

# Wait for Apache to be ready
sleep 2
echo "Apache started successfully"

# Check if DATABASE_URL is set and not empty
if [ -n "$DATABASE_URL" ]; then
  echo "DATABASE_URL detected - configuring database..."

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

  # Load fixtures
  echo "Loading fixtures..."
  php bin/console doctrine:fixtures:load --no-interaction
  echo "Fixtures loaded successfully!"
else
  echo "WARNING: DATABASE_URL not set - skipping database configuration"
  echo "The application may not work correctly without a database"
fi

# Clear cache
php bin/console cache:clear

echo "Setup complete - Apache is running"

# Keep the container running by tailing Apache logs
tail -f /var/log/apache2/access.log /var/log/apache2/error.log
