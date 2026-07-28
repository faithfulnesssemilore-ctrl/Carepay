#!/bin/sh
set -e

export PORT=${PORT:-8080}

if [ -f /etc/nginx/nginx.conf.template ]; then
  envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache

if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
  php artisan admin:create "$ADMIN_EMAIL" "$ADMIN_PASSWORD" \
    --first_name="${ADMIN_FIRST_NAME:-Admin}" \
    --last_name="${ADMIN_LAST_NAME:-User}" \
    --phone="${ADMIN_PHONE:-09000000000}"
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
