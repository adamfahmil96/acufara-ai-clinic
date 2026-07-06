#!/bin/sh
set -e

echo "🚀 Starting Acufara AI Clinic..."

# Clear all caches to ensure fresh start
echo "🧹 Clearing caches..."
php artisan route:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# Run migrations if needed
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "📦 Running migrations..."
    php artisan migrate --force
fi

echo "✅ Ready!"

# Start FrankenPHP
exec "$@"
