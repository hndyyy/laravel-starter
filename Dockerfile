FROM template:image
WORKDIR /var/www/html

RUN apk add php84-exif php84-iconv
COPY . .
COPY nginx/default /etc/nginx/http.d/default.conf

WORKDIR /app
RUN git clone -b dev https://github.com/hndyyy/laravel-starter.git
COPY .env.example /var/www/html/.env

WORKDIR /var/www/html
RUN composer install
RUN php artisan key:generate
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD nginx -g 'daemon off;'
