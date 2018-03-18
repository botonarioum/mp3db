FROM php

RUN apt-get update
RUN apt-get install -y libpq-dev
RUN apt-get install -y git

RUN docker-php-ext-install \
        mbstring \
#        zip \
#        opcache \
        bcmath \
#        pdo pdo_pgsql
