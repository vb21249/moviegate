SHELL := /bin/bash

.PHONY: up down build install migrate seed test codecept lint queue shell

up:
	docker compose up -d --build

down:
	docker compose down --remove-orphans

build:
	docker compose build --no-cache

install:
	docker compose exec php composer install

migrate:
	docker compose exec php php yii migrate --interactive=0

test:
	docker compose exec php vendor/bin/phpunit --configuration phpunit.xml.dist

codecept:
	docker compose exec php vendor/bin/codecept run

lint:
	docker compose exec php find src config commands migrations public -name '*.php' -print0 | xargs -0 -n1 php -l

queue:
	docker compose exec php php yii queue/listen --verbose=1

shell:
	docker compose exec php bash
