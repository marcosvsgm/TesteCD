#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
touch database/database.sqlite

if [ ! -f .env ]; then
    GENERATED_APP_KEY="${APP_KEY:-base64:$(php -r 'echo base64_encode(random_bytes(32));')}"

    cat > .env <<EOF
APP_NAME="DC Tecnologia Vendas"
APP_ENV=${APP_ENV:-production}
APP_KEY=${GENERATED_APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
LOG_CHANNEL=${LOG_CHANNEL:-stderr}
SESSION_DRIVER=${SESSION_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-file}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
DB_CONNECTION=${DB_CONNECTION:-sqlite}
DB_DATABASE=${DB_DATABASE:-/var/www/html/database/database.sqlite}
EOF

    export APP_KEY="${GENERATED_APP_KEY}"
fi

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true

php artisan storage:link --no-interaction || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDER:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
