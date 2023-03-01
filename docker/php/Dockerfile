FROM php:8-fpm

RUN apt update

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt install -y \
	libicu-dev \
	libzip-dev \
	zip \
	git

RUN  docker-php-ext-configure intl \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install zip

WORKDIR /var/www/project
