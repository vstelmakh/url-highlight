.DEFAULT_GOAL := help
.SILENT:
.PHONY: help phpcs phpcs-fix phpstan phpunit phpunit-coverage phpbench check check-full

# Extra arguments forwarded to the underlying tool, example: make phpunit ARGS="--filter=UrlHighlightTest"
ARGS ?=

# Step headline, example: $(HEADLINE) 'Example headline'
# Uses printf, because escape handling in echo differs per shell and may not expand \033
HEADLINE = printf '\n\033[7m \# \033[0m \033[1m%s\033[0m\n'

help: ## Show available commands
	grep -hE '^[a-zA-Z][a-zA-Z-]*:.*## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*## "}; {printf "  \033[32m%-17s\033[0m %s\n", $$1, $$2}'

phpcs: ## Check code style
	$(HEADLINE) 'PHP CS Fixer'
	vendor/bin/php-cs-fixer check --config=phpcs.php --ansi --show-progress=dots --diff $(ARGS)

phpcs-fix: ## Fix code style violations
	$(HEADLINE) 'PHP CS Fixer'
	vendor/bin/php-cs-fixer fix --config=phpcs.php --ansi --show-progress=dots --diff $(ARGS)

phpstan: ## Run static analysis
	$(HEADLINE) 'PHPStan'
	vendor/bin/phpstan analyse --ansi --no-progress $(ARGS)

phpunit: ## Run tests
	$(HEADLINE) 'PHPUnit'
	vendor/bin/phpunit $(ARGS)

phpunit-coverage: ## Run tests and report code coverage
	$(HEADLINE) 'PHPUnit'
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text $(ARGS)

phpbench: ## Run benchmarks
	$(HEADLINE) 'PHPBench'
	vendor/bin/phpbench run --report=custom_compact --time-unit=milliseconds --progress=plain --ansi $(ARGS)

check: ## Run quick check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit

check-full: ## Run full check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit-coverage
	$(MAKE) phpbench
