#!/bin/bash

while ! mysqladmin ping -h"db" -u"admin" -p"laravel" --silent; do
    sleep 1
done

echo "Waiting for MongoDB to be ready..."
while ! mongosh --host mongodb --port 27017 -u admin -p laravel --authenticationDatabase admin --eval "db.runCommand('ping').ok" --quiet; do
    sleep 1
done

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "$(grep '^APP_KEY=' .env)" ] || [ "$(grep '^APP_KEY=' .env | cut -d'=' -f2)" == "" ]; then
    php artisan key:generate
fi

if [ -z "$(grep '^JWT_SECRET=' .env)" ] || [ "$(grep '^JWT_SECRET=' .env | cut -d'=' -f2)" == "" ]; then
    php artisan jwt:secret
fi

php artisan migrate --force

chown -R www-data:www-data storage bootstrap/cache

apache2-foreground 