.PHONY: up migrate setup-db load-fixtures

up: setup-db migrate load-fixtures
	symfony serve -d

setup-db:
	docker compose up -d
	symfony console doctrine:database:create

migrate:
	symfony console make:migration --no-interaction
	symfony console doctrine:migrations:migrate --no-interaction

load-fixtures:
	symfony console doctrine:fixtures:load --no-interaction
