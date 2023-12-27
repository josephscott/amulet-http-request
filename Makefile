SHELL = /bin/bash
.DEFAULT_GOAL := all
HERE := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))

##@ help
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[0-9a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

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
	php -l src/amulet/http/request.php
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
