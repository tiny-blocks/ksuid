---
description: Naming, ordering, inputs, security, and structural rules for all GitHub Actions workflow files.
paths:
  - ".github/workflows/**/*.yml"
  - ".github/workflows/**/*.yaml"
---

# Workflows

Structural and stylistic rules for GitHub Actions workflow files. Refer to `shell-scripts.md` for Bash conventions used
inside `run:` steps, and to `terraforms.md` for Terraform conventions used in `terraform/`.

## Pre-output checklist

Verify every item before producing any workflow YAML. If any item fails, revise before outputting.

1. File name follows the convention: `ci-<runtime>.yml` for reusable CI, `cd-<purpose>.yml` for dispatch CD.
2. `name` field follows the pattern `CI — <Context>` or `CD — <Context>`, using sentence case after the dash
   (e.g., `CD — Run migration`, not `CD — Run Migration`).
3. Reusable workflows use `workflow_call` trigger. CD workflows use `workflow_dispatch` trigger.
4. Each workflow has a single responsibility. CI tests code. CD deploys it. Never combine both.
5. Every input has a `description` field. Descriptions use American English and end with a period.
6. Input names use `kebab-case`: `service-name`, `dry-run`, `skip-build`.
7. Inputs are ordered: required first, then optional. Each group by **name length ascending**.
8. Choice input options are in **alphabetical order**.
9. `env`, `outputs`, and `with` entries are ordered by **key length ascending**.
10. `permissions` keys are ordered by **key length ascending** (`contents` before `id-token`).
11. Top-level workflow keys follow canonical order: `name`, `on`, `concurrency`, `permissions`, `env`, `jobs`.
12. Job-level properties follow canonical order: `if`, `name`, `needs`, `uses`, `with`, `runs-on`,
    `environment`, `timeout-minutes`, `strategy`, `outputs`, `permissions`, `env`, `steps`.
13. All other YAML property names within a block are ordered by **name length ascending**.
14. Jobs follow execution order: `load-config` → `lint` → `test` → `build` → `deploy`.
15. Step names start with a verb and use sentence case: `Setup PHP`, `Run lint`, `Resolve image tag`.
16. Runtime versions are resolved from the service repo's native dependency file (`composer.json`, `go.mod`,
    `package.json`). No version is hardcoded in any workflow.
17. Service-specific overrides live in a pipeline config file (e.g., `.pipeline.yml`) in the service repo,
    not in the workflows repository.
18. The `load-config` job reads the pipeline config file at runtime with safe fallback to defaults when absent.
19. Top-level `permissions` defaults to read-only (`contents: read`). Jobs escalate only the permissions they
    need.
20. AWS authentication uses OIDC federation exclusively. Static access keys are forbidden.
21. Secrets are passed via `secrets: inherit` from callers. No secret is hardcoded.
22. Sensitive values fetched from SSM are masked with `::add-mask::` before assignment.
23. Third-party actions are pinned to the latest available full commit SHA with a version comment:
    `uses: aws-actions/configure-aws-credentials@<latest-sha> # v4.0.2`. Always verify the latest
    version before generating a workflow.
24. First-party actions (`actions/*`) are pinned to the latest major version tag available:
    `actions/checkout@v4`. Always check for the most recent major version before generating a workflow.
25. Production deployments require GitHub Environments protection rules (manual approval).
26. Every job sets `timeout-minutes` to prevent indefinite hangs. CI jobs: 10–15 minutes. CD jobs: 20–30
    minutes. Adjust only with justification in a comment.
27. CI workflows set `concurrency` with `group` scoped to the PR and `cancel-in-progress: true` to avoid
    redundant runs.
28. CD workflows set `concurrency` with `group` scoped to the environment and `cancel-in-progress: false` to
    prevent interrupted deployments.
29. CD workflows use `if: ${{ !cancelled() }}` to allow to deploy after optional build steps.
30. Inline logic longer than 3 lines is extracted to a script in `scripts/ci/` or `scripts/cd/`.

## Style

- All text (workflow names, step names, input descriptions, comments) uses American English with correct
  spelling and punctuation. Sentences and descriptions end with a period.

## Callers

- Callers trigger on `pull_request` targeting `main` only. No `push` trigger.
- Callers in service repos are static (~10 lines) and pass only `service-name` or `app-name`.
- Callers reference workflows with `@main` during development. Pin to a tag or SHA for production.

## Image tagging

- CD deploy builds: `<environment>-sha-<short-hash>` + `latest`.

## Migrations

- Migrations run **before** service deployment (schema first, code second).
- `cd-migrate.yml` supports `dry-run` mode (`flyway validate`) for pre-flight checks.
- Database credentials are fetched from SSM at runtime, never stored in workflow files.
