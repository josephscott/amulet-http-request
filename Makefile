SHELL = bash
.DEFAULT_GOAL := all

.PHONY: all
all: style lint phpstan

# ### #

.PHONY: style
style:
	@echo
	@echo "--> Style: php-cs-fixer"
	vendor/bin/php-cs-fixer fix -v
	@echo

.PHONY: lint
lint:
	@echo
	@echo "--> Lint"
	php -l src/http-request.php
	@echo

.PHONY: phpstan
phpstan:
	@echo
	@echo "--> PHPStan"
	@echo
	vendor/bin/phpstan
