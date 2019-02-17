#!/bin/bash


# BEGIN ########################################################################
echo -e "-- --------------- --\n"
echo -e "-- START PROVISION --\n"
echo -e "-- --------------- --\n"

# VARIABLES ####################################################################
echo -e "-- Setting global variables\n"
APACHE_CONFIG=/etc/apache2/apache2.conf
PHP_INI=/etc/php/7.2/apache2/php.ini
SITES_ENABLED=/etc/apache2/sites-enabled
PHPMYADMIN_CONFIG=/etc/phpmyadmin/config.inc.php
DOCUMENT_ROOT=/var/www/miluz
VIRTUALHOST=miluz.local
LOCALHOST=localhost
MYSQL_APP_DATABASE=miluz
MYSQL_USER=root
MYSQL_PASSWORD=root
MYSQL_APP_USER=symfony
MYSQL_APP_PASSWORD=symfony

# BOX ##########################################################################
echo -e "-- Updating packages list\n"
apt-get update -y -qq

# APACHE #######################################################################
echo -e "-- Installing Apache web server\n"
apt-get install -y apache2

echo -e "-- Adding ServerName to Apache config\n"
grep -q "ServerName ${LOCALHOST}" "${APACHE_CONFIG}" || echo "ServerName ${LOCALHOST}" >> "${APACHE_CONFIG}"

echo -e "-- Allowing Apache override to all\n"
sed -i "s/AllowOverride None/AllowOverride All/g" ${APACHE_CONFIG}

echo -e "-- Updating vhost file\n"
cat > ${SITES_ENABLED}/000-default.conf <<EOF
<VirtualHost *:80>
    ServerName ${VIRTUALHOST}
    ServerAlias www.${VIRTUALHOST}

    DocumentRoot "${DOCUMENT_ROOT}/public"
    <Directory "${DOCUMENT_ROOT}/public">
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    # optionally disable the RewriteEngine for the asset directories
    # which will allow apache to simply reply with a 404 when files are
    # not found instead of passing the request into the full symfony stack
    <Directory "${DOCUMENT_ROOT}/bundles">
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/${VIRTUALHOST}-error.log
    CustomLog ${APACHE_LOG_DIR}/${VIRTUALHOST}-access.log combined

    # optionally set the value of the environment variables used in the application
    #SetEnv APP_ENV prod
    #SetEnv APP_SECRET <app-secret-id>
    #SetEnv DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"
</VirtualHost>
EOF

a2enmod rewrite

# PHP + DEPENDENCIES + MODULES #################################################
apt-get install -y curl unzip
apt-get install -y php7.2 php7.2-cli php7.2-curl php7.2-intl

echo -e "-- PHP.ini changes - Turning PHP error reporting on\n"
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" ${PHP_INI}
sed -i "s/display_errors = .*/display_errors = On/" ${PHP_INI}
sed -i "s/;date.timezone =.*/date.timezone = Europe\/Madrid/" ${PHP_INI}

# MYSQL ########################################################################
echo -e "-- Installing MySQL server\n"
debconf-set-selections <<< "mysql-server mysql-server/root_password password ${MYSQL_PASSWORD}"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${MYSQL_PASSWORD}"

echo -e "-- Installing MySQL packages\n"
apt-get install -y mysql-server mysql-client
apt-get install -y libapache2-mod-auth-mysql
apt-get install -y php7.2-mysqli

echo -e "-- Setting up a MySQL database\n"
mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} -h ${LOCALHOST} -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_APP_DATABASE}"

mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} -h ${LOCALHOST} -e "CREATE USER '${MYSQL_APP_USER}'@'%' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';GRANT ALL PRIVILEGES ON ${MYSQL_APP_DATABASE}.* TO '${MYSQL_APP_USER}'@'%';"

# PHPMYADMIN ###################################################################
echo -e "-- Installing phpMyAdmin GUI\n"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password ${MYSQL_PASSWORD}"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password ${MYSQL_PASSWORD}"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password ${MYSQL_PASSWORD}"
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"

echo -e "-- Installing phpMyAdmin package\n"
apt-get install -y phpmyadmin

echo -e "-- Setting up phpMyAdmin GUI login user\n"
sed -i "s/dbuser='phpmyadmin'/dbuser='${MYSQL_USER}'/g" ${PHPMYADMIN_CONFIG}

# COMPOSER #####################################################################
echo -e "-- Setting up Composer\n"
curl -sSk https://getcomposer.org/installer | php -- --disable-tls
mv composer.phar /usr/local/bin/composer

# REFRESH ######################################################################
echo -e "-- Restarting Apache web server\n"
service apache2 restart

# CUSTOM SETTINGS ##############################################################
echo "cd /var/www/miluz/" >> /home/vagrant/.bashrc

# END ##########################################################################
echo -e "-- ------------- --"
echo -e "-- END PROVISION --"
echo -e "-- ------------- --"