FROM php

RUN apt-get update
RUN apt-get install -y libpq-dev
RUN apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install \
        mbstring \
        bcmath \
        pdo pdo_mysql

RUN php /usr/local/bin/composer install