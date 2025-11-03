# Use a stable PHP 7.4 image with Apache
FROM php:7.4-apache

# Set working directory
WORKDIR /var/www/html

# Enable Apache rewrite module for CodeIgniter
RUN a2enmod rewrite

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install and enable OPcache for better performance
RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache

# Copy CodeIgniter project files
COPY . /var/www/html

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# ✅ Proper Apache config for CodeIgniter (clean and enclosed)
RUN printf "<Directory /var/www/html/>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n" > /etc/apache2/conf-available/codeigniter.conf && \
    a2enconf codeigniter

# ✅ Disable Apache access logs in dev mode (boosts performance)
RUN sed -i 's|CustomLog ${APACHE_LOG_DIR}/access.log combined|# CustomLog disabled for dev|' /etc/apache2/sites-available/000-default.conf

# ✅ Enable OPcache configuration for PHP
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.revalidate_freq=2\n\
opcache.validate_timestamps=1\n\
opcache.save_comments=1\n\
opcache.enable_cli=1" \
> /usr/local/etc/php/conf.d/opcache.ini

# Expose HTTP port
EXPOSE 80
