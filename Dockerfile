FROM template:image
WORKDIR /var/www/html

RUN apk add php84-exif php84-iconv
COPY . .
COPY nginx/default /etc/nginx/http.d/default.conf

RUN composer install
RUN php artisan key:generate

CMD nginx -g 'daemon off;'
