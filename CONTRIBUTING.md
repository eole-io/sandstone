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
- `doc/` Documentation with Jekyll
- `docker/` Docker configuration for Sandstone development (running tests)


## Documentation

This is a jekyll project.

Pages are at the root folder, written in markdown (`doc/*.md`).

There is also full examples pages in `doc/examples/`.

### Preview documentation locally

There is a docker environment to preview documentation locally.

Just go to `doc/`, then run make.

Then go to [http://localhost:4000/sandstone/](http://localhost:4000/sandstone/) (don't forget trailing slash).

See more about documentation's documentation in the readme:

[Documentation Readme](https://github.com/eole-io/sandstone/tree/dev/doc).

### Publishing

You need write access to publish.

Run:

``` bash
make publish
```

It'll ask for a third party access token.
