FROM composer:2.1.3 AS composer

RUN mkdir -p /root/.ssh && \
    chmod 0700 /root/.ssh

COPY gitlab_key/id_rsa /root/.ssh/

RUN chmod 600 /root/.ssh/id_rsa && \
    echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config
RUN cat /root/.ssh/id_rsa

WORKDIR /var/www

COPY . ./
RUN cat /root/.ssh/id_rsa
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts --optimize-autoloader --ignore-platform-reqs --no-dev

FROM registry.ibt.ru:5050/node:16.13.2-ibt-front-npm AS yarn
WORKDIR /var/www/html

COPY ./html/package.json ./package.json
COPY ./html/customCKEditorBuild ./customCKEditorBuild

RUN yarn install

COPY ./html ./
RUN mkdir -p /var/www/public

RUN yarn prod

#FROM wntbrian/php-fpm:libs
#FROM makasim/nginx-php-fpm:7.3-all-exts
FROM registry.ibt.ru:5050/php81:1.5

RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

#RUN apt-get update && apt-get install -y --no-install-recommends --no-install-suggests && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www

COPY --from=composer /var/www/ ./
COPY --from=yarn /var/www/public/assets ./public/assets

RUN chown -R www-data:www-data ./
RUN echo "if [ -d \"database/migrations\" ]; then\n php artisan migrate --force \nfi \nphp artisan optimize \n/cron.sh 2>&1 & \n/entrypoint.sh" > /run.sh
RUN echo "while [ true ]\ndo\n  php /var/www/artisan schedule:run --verbose --no-interaction &\n  sleep 60\ndone" > /cron.sh
RUN chmod +x /cron.sh
RUN chmod +x /run.sh

ENV NGINX_WEB_ROOT=/var/www/public

EXPOSE 80
CMD ["sh", "-c", "/run.sh"]