#!/bin/bash

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install
php artisan migrate
php artisan serve --host=0.0.0.0 --port=80
