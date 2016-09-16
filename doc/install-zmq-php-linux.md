---
layout: page
---

<h1 class="no-margin-top">Install ZMQ and php-zmq extension</h1>

This documentation has been strongly inspired from
[http://alexandervn.nl/2012/05/03/install-zeromq-php-ubuntu/](http://alexandervn.nl/2012/05/03/install-zeromq-php-ubuntu/)
(May 2012)
which installs ZeroMQ 2 on Ubuntu 11.10 and php 5.3.

This tuto installs a ZeroMQ 4 on a Raspberry Pi 3 with minimal Debian Jessie, php7.


## Requirements

Make sure you have all the packages:

``` bash
sudo apt-get install build-essential libtool autoconf uuid-dev pkg-config git libsodium

## and PHP depending on your version, one of these set of packages:
sudo apt-get install php7.0 php7.0-dev
sudo apt-get install php5 php5-dev
sudo apt-get install php php-dev
```

> **Note for Ubuntu**:
> `libsodium` has another package name on ubuntu, it is `libsodium-dev`.
>
> Go to [Jonathan Prass Martins](https://github.com/jonathanpmartins)'s gist to see how to install it:
>
> [https://gist.github.com/jonathanpmartins/2510f38abee1e65c6d92](https://gist.github.com/jonathanpmartins/2510f38abee1e65c6d92)

## Install ZeroMQ

``` bash
wget https://archive.org/download/zeromq_4.1.4/zeromq-4.1.4.tar.gz # Latest tarball on 07/08/2016
tar -xvzf zeromq-4.1.4.tar.gz
cd zeromq-4.1.4
./configure
make
sudo make install
sudo ldconfig
```

> **Note**:
> Check the lastest tarball release here:
> [http://download.zeromq.org/#ZeroMQ_4](http://download.zeromq.org/#ZeroMQ_4)


## Installing the PHP binding

``` bash
git clone git://github.com/mkoppanen/php-zmq.git
cd php-zmq
phpize && ./configure
make
sudo make install
```

Then add `extension=zmq.so` in either:

- your php.ini files (apache2 and cli ones)
- or in file `/etc/php/7.0/mods-available/zmq.ini`, then run `sudo phpenmod zmq`

Do a `sudo service apache2 restart`


## Check that it's well installed

Just create a php file in your `www/` folder and add this:

``` php
<?php

var_dump(class_exists('ZMQContext'));
```

And run the file with `php my-file.php`, and from your browser, you should see `bool(true)`.

You can also check the installed ZMQ version from phpinfo.
