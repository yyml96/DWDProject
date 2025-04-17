# Use the official PHP image with Apache
FROM php:8.1-apache

# Install MongoDB PHP extension and other necessary dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    wget \
    cmake \
    build-essential \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Enable Apache rewrite module
RUN a2enmod rewrite
RUN a2enmod headers

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your project files into the container
COPY . /var/www/html

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 to the host system
EXPOSE 80
