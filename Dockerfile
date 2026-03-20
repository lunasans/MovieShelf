# Stage 1: PHP Dependencies
FROM php:8.2-fpm-alpine AS php_base

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    postgresql-dev \
    libzip-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Stage 2: Node Assets
FROM node:20-alpine AS node_base

WORKDIR /var/www

COPY package.json package-lock.json ./
RUN npm install

COPY . .
RUN npm run build

# Stage 3: Final Image
FROM php_base

WORKDIR /var/www

# Copy application code
COPY . .

# Copy vendor from Stage 1
COPY --from=php_base /var/www/vendor ./vendor

# Copy built assets from Stage 2
COPY --from=node_base /var/www/public/build ./public/build

# Final composer steps
RUN composer dump-autoload --optimize --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
