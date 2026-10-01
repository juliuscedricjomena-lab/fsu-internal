FROM php:8.3-fpm-alpine

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
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    bcmath \
    zip

# Remove build dependencies to reduce image size
RUN apk del $PHPIZE_DEPS

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application code
COPY . /app

# Install application dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-dev --no-interaction; fi

# Set permissions
RUN chown -R www-data:www-data /app

EXPOSE 9000

CMD ["php-fpm"]
