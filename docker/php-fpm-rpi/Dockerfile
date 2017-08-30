FROM alcalyn/rpi-php7-fpm-zmq

# install PHP extensions & PECL modules with dependencies
RUN apt-get update \
 && apt-get install -y \
        bzip2 git wget \
        zlib1g-dev \
        libicu-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install intl \
 && docker-php-ext-install pdo_mysql mysqli \
 && docker-php-ext-install zip

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
 && php composer-setup.php --filename=composer \
 && php -r "unlink('composer-setup.php');" \
 && mv composer /usr/local/bin/composer

WORKDIR "/var/www/html"
