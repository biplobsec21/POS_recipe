# Use a base PHP image with an Apache web server
FROM php:7.4-apache 

# Set the working directory for the container
WORKDIR /var/www/html

# Enable Apache's mod_rewrite for CodeIgniter's .htaccess files
RUN a2enmod rewrite

# Install necessary PHP extensions (like MySQLi for database connection)
RUN docker-php-ext-install mysqli pdo pdo_mysql 

# Copy your existing CodeIgniter project files into the container
# The '.' represents your local project root
COPY . /var/www/html

# Adjust permissions so Apache can read/write files
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (standard HTTP port)
EXPOSE 80