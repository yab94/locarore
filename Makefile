.PHONY: build start stop restart logs css css-watch

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
