FROM php:8.2-apache

# Install mysqli extension for database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Update DocumentRoot to point to the frontend folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/frontend
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files into the container
COPY . /var/www/html/

# Set appropriate permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
