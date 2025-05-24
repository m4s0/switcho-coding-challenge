.DEFAULT_GOAL:=help

.PHONY: bash
bash:
	cd docker && docker-compose exec php-fpm bash

.PHONY: install
install:
	cd docker && docker-compose run --rm php-fpm sh -c 'composer install --no-interaction --no-suggest --ansi'

.PHONY: test
test:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/phpunit --testdox --colors=always'
