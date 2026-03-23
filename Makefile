.PHONY: build start stop restart logs css css-watch db-schema

build:
	docker compose build

start:
	docker compose up -d

stop:
	docker compose down

restart: stop start

logs:
	docker compose logs -f

css:
	npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --minify

css-watch:
	npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --watch

db-schema:
	docker compose exec -T mysql sh -lc 'mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" "$$MYSQL_DATABASE"' < ./sql/schema.sql
