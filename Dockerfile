FROM php:7.4-apache
WORKDIR /var/www/html/
RUN a2enmod rewrite && service apache2 restart
EXPOSE 80