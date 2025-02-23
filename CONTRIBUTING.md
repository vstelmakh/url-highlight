# Contributing

Thank you for the interest in **Url highlight** library. If you find a problem or want to discuss new features
you are welcome to open an [issue](https://github.com/vstelmakh/url-highlight/issues/new)
and/or a [pull request](https://github.com/vstelmakh/url-highlight/compare).

When submitting your pull request, consider writing a description which explains the changes.
Please note that not all pull requests may be merged. Merging a PR is at the discretion of the maintainer
and depends on factors such as: relevance to the project's goals, impact on maintainability and code quality.

By contributing to this project, you agree that your contributions will be licensed under [project's MIT License](./LICENSE).

## Workflow
1. Fork the repository.
2. Start your branch from `master`.
3. Implement your change and add tests for it.
4. Follow the contributing [rules](#rules).
5. Ensure the tests passes. See [tests and tools](#tests-and-tools) for details.
6. Publish pull request and wait for review.

## Rules
Here are a few rules to follow when making changes to this project:
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) code style.
- Take care of complete code coverage with tests.
- Keep documentation up to date with new features and changes.
- Be consistent with existing code in the project.
- Code should properly run on supported PHP versions (see [composer.json](./composer.json) require section).

## Tests and tools
To run all the tests and checks use composer `test:all` script:
```bash
composer test:all
```

For other tools, see scripts section of [composer.json](./composer.json).
