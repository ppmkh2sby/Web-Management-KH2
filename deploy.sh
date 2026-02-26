#!/bin/bash
echo "Running deployment optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm run build
echo "Done."
