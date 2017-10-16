all: run logs

run:
	docker-compose up -d

logs:
	docker-compose logs -ft

bash: run
	docker exec -ti sandstone-doc-jekyll /bin/bash

publish: run
	docker exec -ti sandstone-doc-jekyll /bin/bash -c docker/jekyll/publish.sh
