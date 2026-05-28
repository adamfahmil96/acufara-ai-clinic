# ============================================================
# Stage 1: Shared PHP Runtime
# ============================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS php-base

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

# Install PHP extensions required by Laravel 13, Filament 5, PostgreSQL, and media processing
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

# ============================================================
# Stage 2: Composer Dependencies
# ============================================================
FROM php-base AS vendor

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ============================================================
# Stage 3: Node.js Assets (Frontend Build)
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY resources/ ./resources/
COPY vite.config.js ./

RUN NODE_OPTIONS="--dns-result-order=ipv4first" npm run build

# ============================================================
# Stage 4: Production Image (FrankenPHP)
# ============================================================
FROM php-base AS production

# PHP production configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# The base FrankenPHP Caddyfile imports Caddyfile.d/*.caddyfile.
# Keep a harmless placeholder so local --watch mode does not spam warnings.
RUN mkdir -p /etc/caddy/Caddyfile.d \
    && printf '# Placeholder for optional Caddy snippets.\n' > /etc/caddy/Caddyfile.d/placeholder.caddyfile

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

EXPOSE 80 443 443/udp

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
