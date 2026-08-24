.DEFAULT_GOAL := help
.SILENT:
.PHONY: help phpcs phpcs-fix phpstan phpunit phpunit-coverage phpunit-coverage-clover phpbench check check-full

# Step headline, example: $(HEADLINE) 'Example headline'
# Uses printf, because escape handling in echo differs per shell and may not expand \033
HEADLINE = printf '\n\033[7m \# \033[0m \033[1m%s\033[0m\n'

help: ## Show available commands
	grep -hE '^[a-zA-Z][a-zA-Z-]*:.*## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*## "}; {printf "  \033[32m%-24s\033[0m %s\n", $$1, $$2}'

phpcs: ## Check code style
	$(HEADLINE) 'PHP CS Fixer'
	vendor/bin/php-cs-fixer check --config=phpcs.php --ansi --show-progress=dots --diff

phpcs-fix: ## Fix code style violations
	$(HEADLINE) 'PHP CS Fixer'
	vendor/bin/php-cs-fixer fix --config=phpcs.php --ansi --show-progress=dots --diff

phpstan: ## Run static analysis
	$(HEADLINE) 'PHPStan'
	vendor/bin/phpstan analyse --ansi --no-progress --memory-limit=1G

phpunit: ## Run tests
	$(HEADLINE) 'PHPUnit'
	vendor/bin/phpunit

phpunit-coverage: ## Run tests and report code coverage to standard output
	$(HEADLINE) 'PHPUnit with coverage'
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

phpunit-coverage-clover: ## Run tests and report code coverage in Clover XML format
	$(HEADLINE) 'PHPUnit with coverage (Clover)'
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover var/phpunit/coverage.xml

phpbench: ## Run benchmarks
	$(HEADLINE) 'PHPBench'
	vendor/bin/phpbench run --report=custom_compact --time-unit=milliseconds --progress=plain --ansi

check: ## Run quick check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit

check-full: ## Run full check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit-coverage
	$(MAKE) phpbench
