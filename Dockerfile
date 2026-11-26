FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev unzip \
    && docker-php-ext-install intl mysqli \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN printf 'expose_php=Off\n' > /usr/local/etc/php/conf.d/security.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
COPY . .
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/helpdeskhub-entrypoint
RUN chmod 0755 /usr/local/bin/helpdeskhub-entrypoint \
    && chown -R www-data:www-data writable

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/helpdeskhub-entrypoint"]
