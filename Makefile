SHELL = bash
.DEFAULT_GOAL := all

.PHONY: all
all: style lint phpstan

# ### #

.PHONY: style
style:
	@echo
	@echo "--> style: php-cs-fixer"
	vendor/bin/php-cs-fixer fix -v
	@echo

.PHONY: lint
lint:
	@echo
	@echo "--> lint"
	php -l src/http-request.php
	@echo

.PHONY: phpstan
phpstan:
	@echo
	@echo "--> phpstan"
	@echo
	vendor/bin/phpstan
