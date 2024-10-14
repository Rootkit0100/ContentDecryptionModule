Content Decryption

# Before instalation

Edit the file /etc/php/8.1/fpm/pool.d/bo.conf
and change
php_admin_value[post_max_size] = 100M
php_admin_value[upload_max_filesize] = 100M

Edit the file /etc/php/8.1/cli/php.ini
and change
post_max_size = 100M
upload_max_filesize = 100M

Edit the file /etc/nginx/sites-available/bo.conf
and change client_max_body_size 100M;

After the changess run:
systemctl restart php8.1-fpm
systemctl restart nginx

# After instalation you need to execute
chmod -R 777 /home/onestream/iptv/packages/1s-extra/contentdecryption/bin/
chmod -R 777  /home/onestream/iptv/packages/1s-extra/contentdecryption/storage/


