FROM php:apache

# basic php extensions
RUN apt-get update && apt-get -y install libmcrypt-dev libgd-dev libonig-dev && docker-php-ext-install -j$(nproc) iconv mysqli mbstring opcache pdo pdo_mysql gd

# basic apache config
RUN a2enmod rewrite

# ssl
RUN a2enmod ssl && a2ensite default-ssl && apt-get -y install ssl-cert

# redis
RUN pecl install redis && docker-php-ext-enable redis

# composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e5325b19b381bfd88ce90a5ddb7823406b2a38cff6bb704b0acc289a09c8128d4a8ce2bbafcd1fcbdc38666422fe2806') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && php composer-setup.php && php -r "unlink('composer-setup.php');" && mv composer.phar /usr/local/bin/composer && apt-get -y install git zip unzip

# change DocumentRoot for laravel
RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf && sed -i -e 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/default-ssl.conf

# change timezone
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "Asia/Taipei"\n' > /usr/local/etc/php/conf.d/tzone.ini

# custom php config
COPY config/php.ini /usr/local/etc/php/
