SHELL = bash
.DEFAULT_GOAL := all

.PHONY: all
all: style lint analyze tests

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

.PHONY: analyze
analyze:
	@echo
	@echo "--> Analyze: PHPStan"
	vendor/bin/phpstan

.PHONY: tests
tests:
	@echo
	@echo "--> Tests: Pest"
	vendor/bin/pest
