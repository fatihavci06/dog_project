FROM php:8.2-fpm

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    zip \
    curl \
    # 🔥 IMAGICK bağımlılıkları
    imagemagick \
    libmagickwand-dev \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        curl

# PROD/DEV OPCACHE AYARLARI
# Local geliştirme için validate_timestamps=1 OLMALIDIR.
RUN cat << 'EOF' > /usr/local/etc/php/conf.d/opcache.ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=512
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=60000
opcache.validate_timestamps=1
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Projeyi kopyala
COPY . .
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# İzinleri ayarla (www-data kullanıcısına ver)
RUN chown -R www-data:www-data /var/www/html

# Composer Install
# DİKKAT: --no-dev kaldırıldı çünkü geliştirme ortamındasın ve Pail/Ignition gibi araçlara ihtiyacın var.
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --optimize-autoloader

CMD ["php-fpm", "-F"]
