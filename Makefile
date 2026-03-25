ifneq (,$(wildcard .env))
	include .env
	export
endif

.PHONY: build start stop restart logs css css-watch npm-install db-schema

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

npm-install:
	docker compose --profile tools run --rm node npm install

css:
	docker compose --profile tools run --rm node npm run css:build

css-watch:
	docker compose --profile tools run --rm node npm run css:watch

db-schema:
	docker compose exec -T mysql sh -lc 'mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" "$$MYSQL_DATABASE"' < ./sql/schema.sql

mep-ovh:
	docker compose exec php lftp ${FTP_LOGIN}:${FTP_PASSWORD}@${FTP_HOST}:/ -e "mirror -e -R -x docs* -x TODO* -x tailwind* -x node_modules -x tests -x .env -x .git* -x sql* -x Makefile -x package* -x docker* -x README.md . /locarore ; quit"

php:
	docker compose exec php bash

test:
	docker compose exec php php tests/run.php

test-file:
	docker compose exec php php tests/run.php $(FILE)
