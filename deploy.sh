#!/bin/bash
set -e
echo "=== PPM KH2 - Running deployment optimizations ==="

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=== Optimization complete ==="
