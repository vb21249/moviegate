FROM php:8.3-fpm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libicu-dev \
        libzip-dev \
        libpq-dev \
        libxml2-dev \
        libonig-dev \
        libssl-dev \
        supervisor \
        cron \
    && docker-php-ext-install \
        bcmath \
        intl \
        opcache \
        pdo_mysql \
        soap \
        sockets \
        zip \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && rm -rf /var/lib/apt/lists/*

RUN groupmod -o -g "${APP_GID}" www-data \
    && usermod -o -u "${APP_UID}" -g www-data www-data

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/20-xdebug.ini
COPY docker/php/supervisord.conf /etc/supervisord.conf
COPY docker/php/supervisor/queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf
COPY docker/php/supervisor/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf
COPY docker/php/supervisor/cron.conf /etc/supervisor/conf.d/cron.conf
COPY docker/cron/crontab /etc/cron.d/moviegate

RUN chmod 0644 /etc/cron.d/moviegate

COPY . /app

RUN mkdir -p runtime public/assets tests/_output \
    && chown -R www-data:www-data /app/runtime /app/public/assets /app/tests/_output

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
