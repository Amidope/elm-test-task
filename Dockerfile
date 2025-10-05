FROM php:8.3-fpm-alpine3.20

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache make bash bash-completion

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions pdo_mysql;

ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} devgroup && \
    adduser -D -u ${UID} -G devgroup devuser

ENV TERM=xterm-256color
RUN echo "PS1='\e[92m\u\e[0m@\e[94m\h\e[0m:\e[35m\w\e[0m# '" >> /home/devuser/.bashrc
RUN echo "alias a=\"php artisan\"" >> /home/devuser/.bashrc

WORKDIR /laravel-app

#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
CMD ["tail", "-f", "/dev/null"]
