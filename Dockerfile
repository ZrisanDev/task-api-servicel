FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite headers

COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Limpiar y copiar
RUN rm -rf /var/www/html/*
COPY src/ /var/www/html/

# Arreglar permisos DESPUÉS de copiar
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN find /var/www/html -type f -name "*.php" -exec chmod 644 {} \;

EXPOSE 80