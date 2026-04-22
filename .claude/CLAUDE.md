# Project

PHP library (tiny-blocks ecosystem). Self-contained package: immutable models, zero infrastructure
dependencies in core, small public surface area. Public API at `src/` root; implementation details
under `src/Internal/`.

## Rules

All coding standards, architecture, naming, testing, and documentation conventions
are defined in `rules/`. Read the applicable rule files before generating any code or documentation.

## Commands

- `make test` — run tests with coverage.
- `make mutation-test` — run mutation testing (Infection).
- `make review` — run lint.
- `make help` — list all available commands.

## Post-change validation

After any code change, run `make review`, `make test`, and `make mutation-test`.
If any fails, iterate on the fix while respecting all project rules until all pass.
Never deliver code that breaks lint, tests, or leaves surviving mutants.

## File formatting

Every file produced or modified must:

- Use **LF** line endings. Never CRLF.
- Have no trailing whitespace on any line.
- End with a single trailing newline.
- Have no consecutive blank lines (max one blank line between blocks).
