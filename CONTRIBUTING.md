# Contributing

Thank you for the interest in **Url highlight** library. If you find a problem or want to discuss new features
you are welcome to open an [issue](https://github.com/vstelmakh/url-highlight/issues/new)
and/or a [pull request](https://github.com/vstelmakh/url-highlight/compare).

For a large or breaking change, open an issue first. Agreeing on the direction is easier before the code is written.

When submitting your pull request, consider writing a description which explains the changes.
Please note that not all pull requests may be merged. Merging a PR is at the discretion of the maintainer
and depends on factors such as: relevance to the project's goals, impact on maintainability and code quality.

By contributing to this project, you agree that your contributions will be licensed under
[project's MIT License](./LICENSE).

## Workflow

1. Fork the repository.
2. Start your branch from `main`.
3. Implement your change and add tests for it.
4. Follow the contributing [rules](#rules).
5. Ensure all the checks pass, run `make check`. See [tests and tools](#tests-and-tools) for details.
6. Publish pull request and wait for review.

## Rules

Here are a few rules to follow when making changes to this project:
- Follow the project code style. Run `make phpcs-fix` to apply it.
- Take care of complete code coverage with tests.
- Keep documentation up to date with new features and changes.
- Be consistent with existing code in the project.
- Code should properly run on supported PHP versions (see [composer.json](./composer.json) require section).

## Tests and Tools

All the checks run via [Makefile](./Makefile) targets. Run `make` to see the list:

| Command                 | Description                                                     |
|-------------------------|-----------------------------------------------------------------|
| `make check`            | Code style, static analysis and tests. Use this before a PR.    |
| `make check-full`       | Same as `check`, with code coverage and benchmarks.             |
| `make phpcs`            | Check code style with PHP CS Fixer, config: `phpcs.php`.        |
| `make phpcs-fix`        | Fix code style violations.                                      |
| `make phpstan`          | Run static analysis with PHPStan, config: `phpstan.neon`.       |
| `make phpunit`          | Run tests with PHPUnit, config: `phpunit.xml`.                  |
| `make phpunit-coverage` | Run tests and report code coverage. Requires Xdebug.            |
| `make phpbench`         | Run benchmarks with PHPBench, config: `phpbench.json`.          |

The [CI workflow](./.github/workflows/checks.yml) runs the same targets on every pull request. Tests run on all
supported PHP versions, and with the lowest allowed dependency versions.
