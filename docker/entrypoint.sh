#!/bin/sh
set -eu
cd /var/www/html
php spark migrate --all
php spark db:seed HelpdeskSeeder
chown -R www-data:www-data writable/uploads
exec apache2-foreground
