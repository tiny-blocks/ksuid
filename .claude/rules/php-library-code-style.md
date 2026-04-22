---
description: Pre-output checklist, naming, typing, complexity, and PHPDoc rules for all PHP files in libraries.
paths:
    - "src/**/*.php"
    - "tests/**/*.php"
---

# Code style

Semantic code rules for all PHP files. Formatting rules (PSR-1, PSR-4, PSR-12, line length) are enforced by `phpcs.xml`
and are not repeated here. Refer to `php-library-modeling.md` for library modeling rules.

## Pre-output checklist

Verify every item before producing any PHP code. If any item fails, revise before outputting.

1. `declare(strict_types=1)` is present.
2. All classes are `final readonly` by default. Use `class` (without `final` or `readonly`) only when the class is
   designed as an extension point for consumers (e.g., `Collection`, `ValueObject`). Use `final class` without
   `readonly` only when the parent class is not readonly (e.g., extending a third-party abstract class).
3. All parameters, return types, and properties have explicit types.
4. Constructor property promotion is used.
5. Named arguments are used at call sites for own code, tests, and third-party library methods (e.g., tiny-blocks).
   Never use named arguments on native PHP functions (`array_map`, `in_array`, `preg_match`, `is_null`,
   `iterator_to_array`, `sprintf`, `implode`, etc.) or PHPUnit assertions (`assertEquals`, `assertSame`,
   `assertTrue`, `expectException`, etc.).
6. No `else` or `else if` exists anywhere. Use early returns, polymorphism, or map dispatch instead.
7. No abbreviations appear in identifiers. Use `$index` instead of `$i`, `$account` instead of `$acc`.
8. No generic identifiers exist. Use domain-specific names instead:
   `$data` → `$payload`, `$value` → `$totalAmount`, `$item` → `$element`,
   `$info` → `$currencyDetails`, `$result` → `$conversionOutcome`.
9. No raw arrays exist where a typed collection or value object is available. Use the `tiny-blocks/collection`
   fluent API (`Collection`, `Collectible`) when data is `Collectible`. Use `createLazyFrom` when elements are
   consumed once. Raw arrays are acceptable only for primitive configuration data, variadic pass-through, and
   interop at system boundaries. See "Collection usage" below for the full rule and example.
10. No private methods exist except private constructors for factory patterns. Inline trivial logic at the call site
    or extract it to a collaborator or value object.
11. Members are ordered: constants first, then constructor, then static methods, then instance methods. Within each
    group, order by body size ascending (number of lines between `{` and `}`). Constants and enum cases, which have
    no body, are ordered by name length ascending.
12. Constructor parameters are ordered by parameter name length ascending (count the name only, without `$` or type),
    except when parameters have an implicit semantic order (e.g., `$start/$end`, `$from/$to`, `$startAt/$endAt`),
    which takes precedence. Parameters with default values go last, regardless of name length. The same rule
    applies to named arguments at call sites.
    Example: `$id` (2) → `$value` (5) → `$status` (6) → `$precision` (9).
13. Time and space complexity are first-class design concerns.
    - No `O(N²)` or worse time complexity exists unless the problem inherently requires it and the cost is
      documented in PHPDoc on the interface method.
    - Space complexity is kept minimal: prefer lazy/streaming pipelines (`createLazyFrom`) over materializing
      intermediate collections.
    - Never re-iterate the same source; fuse stages when possible.
    - Public interface methods document time and space complexity in Big O form (see "PHPDoc" section).
14. No logic is duplicated across two or more places (DRY).
15. No abstraction exists without real duplication or isolation need (KISS).
16. All identifiers, comments, and documentation are written in American English.
17. No justification comments exist (`// NOTE:`, `// REASON:`, etc.). Code speaks for itself.
18. `// TODO: <reason>` is used when implementation is unknown, uncertain, or intentionally deferred.
    Never leave silent gaps.
19. All class references use `use` imports at the top of the file. Fully qualified names inline are prohibited.
20. No dead or unused code exists. Remove unreferenced classes, methods, constants, and imports.
21. Never create public methods, constants, or classes in `src/` solely to serve tests. If production code does not
    need it, it does not exist.
22. Always use the most current and clean syntax available in the target PHP version. Prefer match to switch,
    first-class callables over `Closure::fromCallable()`, readonly promotion over manual assignment, enum methods
    over external switch/if chains, named arguments over positional ambiguity (except where excluded by rule 5),
    and `Collection::map` over foreach accumulation.
23. No vertical alignment of types in parameter lists or property declarations. Use a single space between
    type and variable name. Never pad with extra spaces to align columns:
    `public OrderId $id` — not `public OrderId     $id`.
24. Opening brace `{` follows PSR-12: on a **new line** for classes, interfaces, traits, enums, and methods
    (including constructors); on the **same line** for closures and control structures (`if`, `for`, `foreach`,
    `while`, `switch`, `match`, `try`).
25. Never pass an argument whose value equals the parameter's default. Omit the argument entirely.
    Example — `toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE)`:
    `$collection->toArray(keyPreservation: KeyPreservation::PRESERVE)` → `$collection->toArray()`.
    Only pass the argument when the value differs from the default.
26. No trailing comma in any multi-line list. This applies to parameter lists (constructors, methods,
    closures), argument lists at call sites, array literals, match arms, and any other comma-separated
    multi-line structure. The last element never has a comma after it. PHP accepts trailing commas in
    parameter lists, but this project prohibits them for visual consistency.
    Example — correct:
    ```
    new Precision(
        value: 2,
        rounding: RoundingMode::HALF_UP
    );
    ```
    Example — prohibited:
    ```
    new Precision(
        value: 2,
        rounding: RoundingMode::HALF_UP,
    );
    ```

## Casing conventions

- Internal code (variables, methods, classes): **`camelCase`**.
- Constants and enum-backed values when representing codes: **`SCREAMING_SNAKE_CASE`**.

## Naming

- Names describe **what** in domain terms, not **how** technically: `$monthlyRevenue` instead of `$calculatedValue`.
- Generic technical verbs are avoided. See `php-library-modeling.md` — Nomenclature.
- Booleans use predicate form: `isActive`, `hasPermission`, `wasProcessed`.
- Collections are always plural: `$orders`, `$lines`.
- Methods returning bool use prefixes: `is`, `has`, `can`, `was`, `should`.

## Comparisons

1. Null checks: use `is_null($variable)`, never `$variable === null`.
2. Empty string checks on typed `string` parameters: use `$variable === ''`. Avoid `empty()` on typed strings
   because `empty('0')` returns `true`.
3. Mixed or untyped checks (value may be `null`, empty string, `0`, or `false`): use `empty($variable)`.

## American English

All identifiers, enum values, comments, and error codes use American English spelling:
`canceled` (not `cancelled`), `organization` (not `organisation`), `initialize` (not `initialise`),
`behavior` (not `behaviour`), `modeling` (not `modelling`), `labeled` (not `labelled`),
`fulfill` (not `fulfil`), `color` (not `colour`).

## PHPDoc

- PHPDoc is restricted to interfaces only, documenting obligations, `@throws`, and complexity.
- Never add PHPDoc to concrete classes.
- Document `@throws` for every exception the method may raise.
- Document time and space complexity in Big O form. When a method participates in a fused pipeline (e.g., collection
  pipelines), express cost as a two-part form: call-site cost + fused-pass contribution. Include a legend defining
  variables (e.g., `N` for input size, `K` for number of stages).

## Collection usage

When a property or parameter is `Collectible`, use its fluent API. Never break out to raw array functions such as
`array_map`, `array_filter`, `iterator_to_array`, or `foreach` + accumulation. The same applies to `filter()`,
`reduce()`, `each()`, and all other `Collectible` operations. Chain them fluently. Never materialize with
`iterator_to_array` to then pass into a raw `array_*` function.

**Prohibited — `array_map` + `iterator_to_array` on a Collectible:**

```php
$names = array_map(
    static fn(Element $element): string => $element->name(),
    iterator_to_array($collection)
);
```

**Correct — fluent chain with `map()` + `toArray()`:**

```php
$names = $collection
    ->map(transformations: static fn(Element $element): string => $element->name())
    ->toArray(keyPreservation: KeyPreservation::DISCARD);
```
