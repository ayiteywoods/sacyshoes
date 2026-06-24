#!/bin/bash
# Run on the server after SSH login: bash deploy.sh
set -euo pipefail

cd "$(dirname "$0")"

echo "==> Maintenance mode on"
php artisan down --retry=60 || true

echo "==> Pull latest code"
git pull origin main

echo "==> Install PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
    echo "==> Build frontend assets"
    npm ci
    npm run build
else
    echo "==> npm not found — skip asset build (run npm run build locally and pull public/build if needed)"
fi

echo "==> Run migrations"
php artisan migrate --force

echo "==> Storage link"
php artisan storage:link 2>/dev/null || true

echo "==> Cache config/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Maintenance mode off"
php artisan up

echo "==> Deploy complete"
