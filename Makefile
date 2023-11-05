SHELL = bash
.DEFAULT_GOAL := all

.PHONY: all
all: phpstan

# ### #

.PHONY: phpstan
phpstan:
	@echo
	@echo "--> phpstan"
	@echo
	./vendor/bin/phpstan
