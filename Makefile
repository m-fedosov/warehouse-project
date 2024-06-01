setup-docker:
	docker compose down -v || true
	docker compose up -d --build --remove-orphans
	sleep 10

lazy-fix:
	rm -rf migrations/*

up: setup-docker lazy-fix
	symfony console make:migration --no-interaction
	symfony console doctrine:migrations:migrate --no-interaction
	symfony console doctrine:fixtures:load --no-interaction --group=AppFixtures
	symfony serve -d

tests: setup-docker lazy-fix
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console make:migration --no-interaction
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test --group=TestFixtures
	symfony php bin/phpunit

.PHONY: setup-docker lazy-fix up tests