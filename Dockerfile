FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /app

# Install system dependencies including build tools
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    mysql-client \
    libzip-dev \
    redis \
    $PHPIZE_DEPS

# Install PHP extensions including Redis
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    bcmath \
    zip

# Install PHP Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

# Remove build dependencies to reduce image size
RUN apk del $PHPIZE_DEPS

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application code
COPY . /app

# Install application dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-dev --no-interaction; fi

# Create necessary directories with proper permissions
RUN mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/inertia-devtools \
    && mkdir -p storage/app/temp \
    && mkdir -p bootstrap/cache

# Set permissions for Laravel
RUN chown -R www-data:www-data /app && \
    chmod -R 755 storage && \
    chmod -R 755 bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
