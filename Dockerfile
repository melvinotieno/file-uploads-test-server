FROM php:8.4-apache

WORKDIR /var/www/html

COPY . /var/www/html

RUN chmod -R 777 /var/www/html/uploads
