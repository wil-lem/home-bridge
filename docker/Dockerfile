FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    mariadb-client \
    nano \
    iputils-ping

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Set www-data to use the provided UID/GID
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data \
    && usermod -d /app www-data \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p /app/.composer \
    && chown -R www-data:www-data /app/.composer

# Create a symbolic link to ensure mysql command is available
RUN ln -sf /usr/bin/mysql /usr/local/bin/mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install additional extensions for Laravel
RUN docker-php-ext-install opcache

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

# Set Composer environment variables
ENV COMPOSER_HOME=/app/.composer
ENV PATH="${PATH}:/app/.composer/vendor/bin"

WORKDIR /app