# === Stage 1: Assets Builder ===
FROM node:20-alpine AS assets-builder
WORKDIR /app

# Copy files needed to build frontend assets
COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources

# Install dependencies and build assets
RUN npm ci && npm run build

# === Stage 2: Main Production Container ===
FROM dunglas/frankenphp:1-php8.4

# Set environment variables
ENV CAP_NET_BIND_SERVICE=1
ENV LARAVEL_PROD=true

WORKDIR /app

# Install system dependencies, python3, pip and build tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    python3-pip \
    python3-setuptools \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions using the native script provided by FrankenPHP
RUN install-php-extensions gd zip pdo_mysql opcache intl pcntl bcmath
# Install python packages (pyhanko and pyhanko-certvalidator)
# Note: --break-system-packages is safe and required in newer Debian versions inside a Docker container
RUN pip3 install --break-system-packages pyhanko pyhanko-certvalidator

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files (respecting .dockerignore)
COPY . .

# Copy compiled assets from Stage 1
COPY --from=assets-builder /app/public/build ./public/build

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set correct permissions for Laravel
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public/certificados/borradores \
    storage/app/public/certificados/firmados \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Use default production PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure FrankenPHP Caddy server configuration if needed, or run default
# We expose port 80 (FrankenPHP default)
EXPOSE 80
EXPOSE 443

# Start FrankenPHP using the public folder as root
ENTRYPOINT ["/usr/local/bin/frankenphp"]
CMD ["run", "--config", "/etc/caddy/Caddyfile"]
