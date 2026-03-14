#!/usr/bin/env bash
# Tambah User
#useradd $SSH_USER --create-home --password "$(openssl passwd -1 "$SSH_PASSWORD")" && echo "$SSH_USER ALL=(ALL:ALL) ALL" >> /etc/sudoers
adduser -h /home/$SSH_USER -s /bin/ash -D $SSH_USER
echo "$SSH_USER:$(openssl passwd -1 "$SSH_PASSWORD")" | chpasswd -e
echo "$SSH_USER ALL=(ALL) ALL" >> /etc/sudoers.d/$SSH_USER
adduser $SSH_USER nginx
# Timezone (Zona Waktu)
rm -f /etc/localtime;  ln -s /usr/share/zoneinfo/$TIMEZONE /etc/localtime

# Ownership
#chown -R nginx:nginx /var/www/html

service php-fpm84 restart && service sshd $SSH_SERVICE

#php artisan key:generate 
php artisan migrate --seed
exec "$@"

