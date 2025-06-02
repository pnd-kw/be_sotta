FROM php:8.2-fpm

# Install system dependencies dan ekstensi PHP termasuk pdo_pgsql (untuk PostgreSQL)
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    vim \
    nano \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy seluruh aplikasi ke container
COPY . /var/www

# Install dependency Laravel tanpa dev dependencies & optimize autoloader
RUN composer install --no-dev --optimize-autoloader

# Set permission untuk storage dan cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Cache konfigurasi Laravel agar cepat
RUN php artisan config:cache

# Expose port 8000 agar dapat diakses dari luar container
EXPOSE 8000

# Jalankan migrate + seeder, lalu serve Laravel
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000
