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
tests: test-server
	@echo
	@echo "--> Tests: Pest"
	@echo
	# Always stop the web server, even if tests fail
	bash -c "./vendor/bin/pest || kill -9 `cat tests/server/pid.txt`"
	@echo
	@echo "--> Test Server: stopping"
	@echo
	kill -9 `cat tests/server/pid.txt`

.PHONY: test-server
test-server:
	@echo
	@echo "--> Test Server: starting"
	@echo
	nohup php -S 127.0.0.1:7878 -t tests/server > /dev/null 2>&1 & echo "$$!" > tests/server/pid.txt
