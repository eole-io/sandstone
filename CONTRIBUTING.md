Contributing
============

The project is open for contributors pull requests or issues.


## Install the project locally

Sandstone requires:

 - PHP 5.6+ or PHP 7
 - ZeroMQ
 - php-zmq PHP extension

But it also provides a docker environment for development,
so you can use docker and docker-compose instead of all the above dependencies.

``` bash
git clone git@github.com:eole-io/sandstone.git
cd sandstone/

# Without docker
composer install

# With docker
make install
```


## Run tests

``` bash
# Install dependencies
composer install

# Running tests
vendor/bin/phpunit -c .

# Checking code style
vendor/bin/phpcs src --standard=phpcs.xml
```

Or using Docker, install dependencies and run all tests and codestyle checks:

``` bash
make
```


## Project structure

- `src/` Source code
- `tests/` Unit tests and functionnal tests with PHPUnit
- `docker/` Docker configuration for Sandstone development (running tests)


## Documentation

Documentation source code is at <https://github.com/eole-io/sandstone-doc>.

See [documentation contributing](https://github.com/eole-io/sandstone-doc/blob/master/CONTRIBUTING.md) page.
