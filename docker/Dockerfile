FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (minimal set for Laravel API)
RUN docker-php-ext-install mbstring pcntl bcmath zip opcache

# Set www-data to use the provided UID/GID
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data \
    && usermod -d /app www-data \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p /app/.composer \
    && chown -R www-data:www-data /app/.composer

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set Composer environment variables
ENV COMPOSER_HOME=/app/.composer
ENV PATH="${PATH}:/app/.composer/vendor/bin"

WORKDIR /app