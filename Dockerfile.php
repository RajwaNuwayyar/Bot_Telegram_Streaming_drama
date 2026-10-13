FROM php:8.2-apache

# Install ekstensi mysqli dan pdo_mysql untuk koneksi database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache (berguna untuk routing URL jika diperlukan)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html