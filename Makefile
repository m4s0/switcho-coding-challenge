.DEFAULT_GOAL:=help

REGEX = '(?<=\DB_VOLUME_NAME=)[a-zA-Z0-9\._-]*'
VOLUME := $(shell cat docker/.env | grep -oP ${REGEX})

.PHONY: build
build:
	cd docker && docker-compose build

.PHONY: up
up:
	cd docker && docker-compose up -d

.PHONY: down
down:
	cd docker && docker-compose down

.PHONY: rm-db
rm-db:
	cd docker && docker volume rm ${VOLUME}

.PHONY: logs
logs:
	cd docker && docker-compose logs -f

.PHONY: bash
bash:
	cd docker && docker-compose exec php-fpm bash
