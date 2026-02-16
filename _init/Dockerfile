# =============================================================================
# Stage 1: Build frontend assets
# =============================================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.ts tsconfig.json eslint.config.js ./
COPY public/ public/

ENV DOCKER_BUILD=true
RUN npm run build

# =============================================================================
# Stage 2: Install PHP dependencies
# =============================================================================
FROM composer:2.8 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize

# =============================================================================
# Stage 3: Production image
# =============================================================================
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    curl \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache \
    && rm -rf /var/cache/apk/*

# Configure PHP for production
COPY _init/docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY _init/docker/php/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini

WORKDIR /var/www/html

# Copy application code from composer stage (includes vendor/)
COPY --from=composer /app /var/www/html

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/public/build public/build

# Create storage directories and set permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY _init/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
