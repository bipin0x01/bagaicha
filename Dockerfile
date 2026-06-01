FROM php:8.2-apache

# Enable Apache Rewrite module
RUN a2enmod rewrite

# Setup custom PHP configurations
RUN echo "file_uploads = On\n\
memory_limit = 256M\n\
upload_max_filesize = 64M\n\
post_max_size = 64M\n\
max_execution_time = 600" > /usr/local/etc/php/conf.d/custom.ini

# Grant server user full ownership of Apache directory
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
