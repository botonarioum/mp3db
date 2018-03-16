FROM php

RUN docker-php-ext-install \
        mbstring \
        zip \
        opcache \
        bcmath \
        pdo pdo_pgsql
