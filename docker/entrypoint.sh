#!/bin/sh
set -e

# Cache framework config/routes/views for production performance.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Apply pending migrations (idempotent; tolerate a transient DB hiccup on cold start).
php artisan migrate --force || echo "Migration step skipped (DB not ready?), continuing..."

# Serve on the port Render assigns (defaults to 10000 locally).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
