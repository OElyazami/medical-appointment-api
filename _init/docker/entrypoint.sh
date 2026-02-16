#!/bin/sh
set -e

echo "Starting application..."

# Fix storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create .env if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
  echo "Creating .env from .env.example..."
  cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate APP_KEY if not set
if ! grep -q 'APP_KEY=base64:' /var/www/html/.env; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

# Clear any stale cache first
php artisan config:clear 2>/dev/null || true

# Cache configuration for production (now APP_KEY is in .env)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed doctors (only if the doctors table is empty)
php artisan db:seed --class=DoctorSeeder --force

# Fix permissions again after cache/log files are created
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "Application ready."

exec "$@"
