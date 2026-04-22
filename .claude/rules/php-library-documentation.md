---
description: Standards for README files and all project documentation in PHP libraries.
paths:
    - "**/*.md"
---

# Documentation

## README

1. Include an anchor-linked table of contents.
2. Start with a concise one-line description of what the library does.
3. Include a **badges** section (license, build status, coverage, latest version, PHP version).
4. Provide an **Overview** section explaining the problem the library solves and its design philosophy.
5. **Installation** section: Composer command (`composer require vendor/package`).
6. **How to use** section: complete, runnable code examples covering the primary use cases. Each example
   includes a brief heading describing what it demonstrates.
7. If the library exposes multiple entry points, strategies, or container types, document each with its own
   subsection and example.
8. **FAQ** section: include entries for common pitfalls, non-obvious behaviors, or design decisions that users
   frequently ask about. Each entry is a numbered question as heading (e.g., `### 01. Why does X happen?`)
   followed by a concise explanation. Only include entries that address real confusion points.
9. **License** and **Contributing** sections at the end.
10. Write strictly in American English. See `php-library-code-style.md` American English section for spelling
    conventions.

## Structured data

1. When documenting constructors, factory methods, or configuration options with more than 3 parameters,
   use tables with columns: Parameter, Type, Required, Description.
2. Prefer tables to prose for any structured information.

## Style

1. Keep language concise and scannable.
2. Never include placeholder content (`TODO`, `TBD`).
3. Code examples must be syntactically correct and self-contained.
4. Code examples include every `use` statement needed to compile. Each example stands alone — copyable into
   a fresh file without modification.
5. Do not document `Internal/` classes or private API. Only document what consumers interact with.
