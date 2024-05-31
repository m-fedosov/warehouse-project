up: setup-db migrate load-fixtures
	symfony serve -d

setup-db:
	docker compose down -v || true
	docker compose up -d --remove-orphans
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create

migrate:
	symfony console make:migration --no-interaction
	symfony console doctrine:migrations:migrate --no-interaction

load-fixtures:
	symfony console doctrine:fixtures:load --no-interaction

tests:
	docker compose down -v || true
	docker compose up -d --build --remove-orphans
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console make:migration --no-interaction
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	symfony php bin/phpunit $(MAKECMDGOALS)

.PHONY: up migrate setup-db load-fixtures tests