# ============================================================
# Stage 1: Composer Dependencies
# ============================================================
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ============================================================
# Stage 2: Node.js Assets (Frontend Build)
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./

RUN npm run build

# ============================================================
# Stage 3: Production Image (FrankenPHP)
# ============================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libexif-dev \
    libpq-dev \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-dev

# Install PHP extensions
RUN install-php-extensions \
    gd \
    exif \
    pgsql \
    pdo_pgsql \
    pcntl \
    intl \
    zip \
    opcache \
    redis \
    bcmath

# Configure GD with full image support
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# PHP production configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy Composer vendor from vendor stage
COPY --from=vendor /app/vendor ./vendor

# Copy built assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Copy application code
COPY . .

# Set correct permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

EXPOSE 80 443 443/udp

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
