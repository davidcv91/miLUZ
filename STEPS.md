``composer create-project symfony/website-skeleton miluz``

``git init``

``git add .``

``git commit -m "Initial commit"``

* Add virtual host
```
<VirtualHost *:80>
    ServerName miluz.local
    ServerAlias www.miluz.local

    DocumentRoot "C:/cygwin64/home/david/miluz/public"
    <Directory "C:/cygwin64/home/david/miluz/public">
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
    <Directory "C:/cygwin64/home/david/miluz/bundles">
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog logs/miluz_error.log
    CustomLog logs/miluz_access.log combined

    # optionally set the value of the environment variables used in the application
    #SetEnv APP_ENV prod
    #SetEnv APP_SECRET <app-secret-id>
    #SetEnv DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"
</VirtualHost>
```

* Add to hosts:

``127.0.0.1 miluz.local``

* Routes always enclosed by double quotes

``composer require --dev doctrine/doctrine-fixtures-bundle``

* Review or create `.env` file