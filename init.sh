#!/usr/bin/env bash
# Script de inicialización del proyecto (ejecutar una sola vez)
# Uso: docker-compose exec app bash init.sh

set -e

echo "==> Instalando dependencias de Composer..."
composer install

echo "==> Instalando paquetes adicionales..."
composer require laudis/neo4j-php-client
composer require laravel/breeze --dev

echo "==> Generando clave de aplicación..."
php artisan key:generate

echo "==> Instalando Laravel Breeze con Vue + Inertia..."
php artisan breeze:install vue --inertia --no-interaction

echo ""
echo "✔ Backend listo."
echo ""
echo "Ahora ejecuta en el contenedor 'node':"
echo "  docker-compose exec node sh -c 'npm install && npm run build'"
echo ""
echo "O para desarrollo con HMR:"
echo "  docker-compose exec node sh -c 'npm install && npm run dev'"
