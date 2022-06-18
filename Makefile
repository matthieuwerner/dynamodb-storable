#!/usr/bin/make -f

COMPOSER_IMAGE=composer:2.3.7
DOCKER_RUN=docker run -it --rm --name dynamo-db-storage -v "$$PWD":/usr/src/myapp -w /usr/src/myapp $(COMPOSER_IMAGE)
COMPOSER_RUN=$(DOCKER_RUN) composer

.DEFAULT_GOAL := help

help:           ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## Install project dependencies
	$(COMPOSER_RUN) install

update:        ## Update project dependencies
	$(COMPOSER_RUN) update

cs-fix:         ## Run php cs fixer (fix)
	$(DOCKER_RUN) vendor/bin/php-cs-fixer fix -vvv --diff .

cs-check:       ## Run php cs fixer (check)
	$(COMPOSER_RUN) cs-fixer

phpstan:        ## Run PHPStan
	$(COMPOSER_RUN) phpstan

phpunit:        ## Run PHPUnit
	$(COMPOSER_RUN) phpunit
