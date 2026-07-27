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

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
