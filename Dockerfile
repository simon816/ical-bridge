FROM php:8.1.11-fpm

LABEL description="RSS-Bridge but for iCalendar."
LABEL repository="https://github.com/simon816/ical-bridge"
LABEL website="https://github.com/simon816/ical-bridge"

RUN apt-get update && \
    apt-get install --yes --no-install-recommends \
      nginx \
      && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Cannot use distro provided composer due to not using distro php
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/bin --filename=composer && chmod +x /usr/bin/composer 

COPY ./docker/nginx.conf /etc/nginx/sites-enabled/default

COPY --chown=www-data:www-data ./ /app/

RUN cd /app && composer install --no-dev --optimize-autoloader

EXPOSE 80

ENTRYPOINT nginx && php-fpm
