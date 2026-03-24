ifneq (,$(wildcard .env))
	include .env
	export
endif

.PHONY: build start stop restart logs css css-watch db-schema

build:
	docker compose build

start:
	docker compose up -d

stop:
	docker compose down

stop-all:
	docker stop $(docker ps -a -q)

restart: stop start

logs:
	docker compose logs -f

css:
	npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --minify

css-watch:
	npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --watch

db-schema:
	docker compose exec -T mysql sh -lc 'mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" "$$MYSQL_DATABASE"' < ./sql/schema.sql

mep-ovh:
	docker compose exec php lftp ${FTP_LOGIN}:${FTP_PASSWORD}@${FTP_HOST}:/ -e "mirror -e -R -x .env -x .git* -x sql* -x docker* . /locarore ; quit"

php:
	docker compose exec php bash

test:
	docker compose exec php php tests/run.php

test-file:
	docker compose exec php php tests/run.php $(FILE)
