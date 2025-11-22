FROM php:8.2-fpm

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd curl

# PROD OPCACHE AYARLARI
RUN cat << 'EOF' > /usr/local/etc/php/conf.d/opcache.ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=512
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=60000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Projeyi kopyala
COPY . .

# Composer Install (prod)
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
