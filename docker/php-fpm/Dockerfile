FROM php:8.2-fpm-alpine

RUN apk --update --no-cache add bash \
   && apk add wget \
   && apk add git \
   && apk add libpq-dev \
   && apk add icu-dev

RUN docker-php-ext-install pdo \
    pdo_pgsql \
    intl \
    opcache

RUN apk add --update linux-headers

# Install Xdebug
RUN apk add --no-cache $PHPIZE_DEPS \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug

COPY php-overrides.ini /usr/local/etc/php/conf.d/
COPY timezone.ini /usr/local/etc/php/conf.d/
COPY xdebug.ini /usr/local/etc/php/conf.d/

RUN apk add alpine-conf
RUN setup-timezone -z Europe/Rome

RUN mkdir -p /var/app
RUN adduser --disabled-password app && chown -R app:app /var/app

# Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Symfony installer
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

USER app
WORKDIR /var/app

EXPOSE 9000

RUN echo "alias ll='ls -l'" >> ~/.bashrc
RUN echo "alias la='ls -la'" >> ~/.bashrc
