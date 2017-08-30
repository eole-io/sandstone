all: install test

install:
	docker-compose up -d

	docker exec -ti sandstone sh -c "composer install"

update:
	docker-compose up -d

	docker exec -ti sandstone sh -c "composer update"

test:
	docker-compose up -d

	docker exec -ti sandstone sh -c "vendor/bin/phpunit -c ."
	docker exec -ti sandstone sh -c "vendor/bin/phpcs src --standard=phpcs.xml"

logs:
	docker-compose logs -ft

bash:
	docker exec -ti sandstone bash
