.DEFAULT_GOAL := help
.SILENT:
.PHONY: help phpcs phpcs-fix phpstan phpunit phpunit-coverage phpunit-coverage-clover \
	phpbench phpbench-baseline phpbench-compare check check-full

# Step headline, example: $(HEADLINE) 'Example headline'
# Uses printf, because escape handling in echo differs per shell and may not expand \033
HEADLINE = printf '\n\033[7m \# \033[0m \033[1m%s\033[0m\n'

# Xdebug off, because it slows every tool down by about a quarter even when no debugging session is active
# Memory limit is high enough for analysis and coverage, and low enough that a runaway fails fast
PHP = XDEBUG_MODE=off php -d memory_limit=1G
PHP_COVERAGE = XDEBUG_MODE=coverage php -d memory_limit=1G

help: ## Show available commands
	grep -hE '^[a-zA-Z][a-zA-Z-]*:.*## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*## "}; {printf "  \033[32m%-24s\033[0m %s\n", $$1, $$2}'

check: ## Run quick check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit

check-full: ## Run full check
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) phpunit-coverage
	$(MAKE) phpbench

phpcs: ## Check code style
	$(HEADLINE) 'PHP CS Fixer'
	$(PHP) vendor/bin/php-cs-fixer check --config=phpcs.php --ansi --show-progress=dots --diff

phpcs-fix: ## Fix code style violations
	$(HEADLINE) 'PHP CS Fixer'
	$(PHP) vendor/bin/php-cs-fixer fix --config=phpcs.php --ansi --show-progress=dots --diff

phpstan: ## Run static analysis
	$(HEADLINE) 'PHPStan'
	$(PHP) vendor/bin/phpstan analyse --ansi --no-progress

phpunit: ## Run tests
	$(HEADLINE) 'PHPUnit'
	$(PHP) vendor/bin/phpunit

phpunit-coverage: ## Run tests and report code coverage to standard output
	$(HEADLINE) 'PHPUnit with coverage'
	$(PHP_COVERAGE) vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

phpunit-coverage-clover: ## Run tests and report code coverage in Clover XML format
	$(HEADLINE) 'PHPUnit with coverage (Clover)'
	$(PHP_COVERAGE) vendor/bin/phpunit --coverage-clover var/phpunit/coverage.xml

phpbench: ## Run benchmarks
	$(HEADLINE) 'PHPBench'
	$(PHP) vendor/bin/phpbench run --report=custom_compact

phpbench-baseline: ## Run benchmarks and store the result to compare against
	$(HEADLINE) 'PHPBench (store baseline)'
	$(PHP) vendor/bin/phpbench run --report=custom_compact --store --tag=baseline

phpbench-compare: ## Run benchmarks and compare against the stored baseline
	$(HEADLINE) 'PHPBench (compare to baseline)'
	$(PHP) vendor/bin/phpbench run --report=custom_compact --ref=baseline \
		--assert='mode(variant.time.avg) < mode(baseline.time.avg) * 1.1' \
		--assert='mode(variant.mem.peak) < mode(baseline.mem.peak) * 1.1'
