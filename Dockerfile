FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    procps \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN printf "memory_limit=512M\nmax_execution_time=0\n" > /usr/local/etc/php/conf.d/99-memory.ini

WORKDIR /laravel-app

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
