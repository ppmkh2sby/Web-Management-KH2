#!/usr/bin/env bash
set -e

echo "=== PPM KH2 - Running deployment optimizations ==="

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Optimization complete ==="