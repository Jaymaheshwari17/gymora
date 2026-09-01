FROM webdevops/php-nginx:8.2-alpine

WORKDIR /app

RUN apk add --no-cache nodejs npm yarn

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN yarn install && yarn build

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN chown -R application:application storage bootstrap/cache

ENV WEB_DOCUMENT_ROOT=/app/public
ENV PHP_DISPLAY_ERRORS=1

EXPOSE 80

CMD ["supervisord"]