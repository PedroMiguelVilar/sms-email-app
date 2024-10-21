# Use the official PHP image as a base for building dependencies
FROM php:8.2-apache as build

# Install required dependencies for PHP and Composer
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev zip unzip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install Composer (Download from source)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer

# Install dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Production image
FROM php:8.2-apache

# Install required extensions
RUN apt-get update && apt-get install -y libicu-dev zip unzip curl \
    && docker-php-ext-install intl pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration file
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy application files and vendor from the build stage
COPY --from=build /var/www/html /var/www/html

# Copy Composer from the build stage
COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

# Expose port 80
EXPOSE 80

# Set the default command to run Apache
CMD ["apache2-foreground"]


#At the end be sure to install the vendor dependencies with `docker-compose run app composer install`