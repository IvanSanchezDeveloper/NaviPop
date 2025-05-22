FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony


WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies via Composer
RUN composer install

# Expose port for Symfony server
EXPOSE 8000

# Default command
CMD ["symfony", "serve", "--allow-http", "--allow-all-ip", "--no-tls", "--port=8000"]
