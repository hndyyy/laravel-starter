FROM template:image
RUN apk update
RUN apk add php84-exif php84-iconv
WORKDIR /var/www/html

COPY . .
#RUN composer require nasirkhan/laravel-cube
RUN composer install
RUN php artisan key:generate
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R nginx:nginx storage bootstrap/cache
COPY nginx/default /etc/nginx/http.d/default.conf
CMD nginx -g 'daemon off;'
