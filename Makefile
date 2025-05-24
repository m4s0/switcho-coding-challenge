.DEFAULT_GOAL:=help

.PHONY: bash
bash:
	cd docker && docker-compose exec php-fpm bash
