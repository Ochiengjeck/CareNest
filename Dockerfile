FROM php:8.4-cli-alpine

# Use mlocati/php-extension-installer for pre-compiled extensions (much faster)
    COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

#Install system dependencies + PHP extensions in one layer
RUN apk add --no-cache bash git nodejs npm \
    && install-php-extensions \
        pdo_pgsql \
        pdo_sqlite \
        gd \
        mbstring \
        zip \
        opcache \
        bcmath \
        pcntl \
        xml \
        ctype \
        fileinfo \
        tokenizer

 # Install Composer 2
 COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

 WORKDIR /app

 # Copy composer files first (layer cache)
 COPY composer.json composer.lock ./
 RUN composer install \
     --optimize-autoloader \
     --no-scripts \
     --no-interaction \
     --no-dev

 # Copy package files and build frontend (layer cache)
 COPY package.json package-lock.json ./
 RUN npm ci --ignore-scripts

 # Copy application source
 COPY . .

 # Discover packages now that source is present
 RUN php artisan package:discover --ansi

 # Build frontend assets
 RUN npm run build

 # Create required storage directories
 RUN mkdir -p \
     storage/app/public/cms/images \
     storage/app/public/cms/downloads \
     storage/app/public/cms/news \
     storage/app/public/cms/leadership \
     storage/app/private/receipts \
     storage/app/private/roo \
     storage/app/private/uploads \
     storage/app/private/strategic-plans \
     storage/framework/cache/data \
     storage/framework/sessions \
     storage/framework/views \
     storage/logs \
     bootstrap/cache \
     && chmod -R 775 storage bootstrap/cache

 EXPOSE 8000

 CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]