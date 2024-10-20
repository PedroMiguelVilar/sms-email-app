# Use the official PHP image as a base for building dependencies
FROM php:8.2-apache as build

# Install required dependencies for PHP and Composer
# Install dependencies in a single RUN command
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Copy application files
WORKDIR /app
COPY . /app

# Install Composer
COPY --from=composer:2.7.8 /usr/bin/composer /usr/bin/composer

# Run Composer install
RUN composer install --no-dev --optimize-autoloader

# Production image
FROM php:8.2-apache

# Install intl and pdo_mysql extensions
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-install intl pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy Apache configuration file
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy only necessary files from the build stage
COPY --from=build /app /var/www/html

# Expose port 80
EXPOSE 80

# Set the default command to run Apache
CMD ["apache2-foreground"]
