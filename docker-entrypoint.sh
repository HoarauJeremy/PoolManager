#!/bin/bash
set -e

npm run build

php bin/console cache:clear

# Start Apache
exec apache2-foreground
