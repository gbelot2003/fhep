FROM php:5.6.18-apache 

RUN docker-php-ext-install mysqli

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN service apache2 restart