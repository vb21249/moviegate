FROM php:8.3-fpm

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
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/supervisord.conf /etc/supervisord.conf
COPY docker/php/supervisor/queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf
COPY docker/php/supervisor/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf
COPY docker/cron/crontab /etc/cron.d/moviegate

RUN chmod 0644 /etc/cron.d/moviegate && crontab /etc/cron.d/moviegate

COPY . /app

RUN mkdir -p runtime web/assets tests/_output \
    && chown -R www-data:www-data /app/runtime /app/web/assets /app/tests/_output

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
