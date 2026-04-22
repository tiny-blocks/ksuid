---
description: Library modeling rules — folder structure, public API boundary, naming, value objects, exceptions, enums, extension points, and complexity.
paths:
    - "src/**/*.php"
---

# Library modeling

Libraries are self-contained packages. The core has no dependency on frameworks, databases, or I/O. Refer to
`php-library-code-style.md` for the pre-output checklist applied to all PHP code.

## Folder structure

```
src/
├── <PublicInterface>.php         # Primary contract for consumers
├── <Implementation>.php          # Main implementation or extension point
├── <Enum>.php                    # Public enum
├── Contracts/                    # Interfaces for data returned to consumers
├── Internal/                     # Implementation details (not part of public API)
│   ├── <Collaborator>.php
│   └── Exceptions/               # Internal exception classes
├── <Feature>/                    # Feature-specific subdirectory when needed
└── Exceptions/                   # Public exception classes (when part of the API)
```

Never use `Models/`, `Entities/`, `ValueObjects/`, `Enums/`, or `Domain/` as folder names.

## Public API boundary

Only interfaces, extension points, enums, and thin orchestration classes live at the `src/` root. These classes
define the contract consumers interact with and delegate all real work to collaborators inside `src/Internal/`.
If a class contains substantial logic (algorithms, state machines, I/O), it belongs in `Internal/`, not at the root.

The `Internal/` namespace signals classes that are implementation details. Consumers must not depend on them.
Breaking changes inside `Internal/` are not semver-breaking for the library.

## Nomenclature

1. Every class, property, method, and exception name reflects the **concept** the library represents. A math library
   uses `Precision`, `RoundingMode`; a money library uses `Currency`, `Amount`; a collection library uses
   `Collectible`, `Order`.
2. Name classes after what they represent: `Money`, `Color`, `Pipeline` — not after what they do technically.
3. Name methods after the operation in the library's vocabulary: `add()`, `convertTo()`, `splitAt()`.

### Always banned

These names carry zero semantic content. Never use them anywhere, as class suffixes, prefixes, or method names:

- `Data`, `Info`, `Utils`, `Item`, `Record`, `Entity`.
- `Exception` as a class suffix (e.g., `FooException` — use `Foo` when it already extends a native exception).

### Anemic verbs (banned by default)

These verbs hide what is actually happening behind a generic action. Banned unless the verb **is** the operation
that constitutes the library's reason to exist (e.g., a JSON parser may have `parse()`; a hashing library may
have `compute()`):

- `ensure`, `validate`, `check`, `verify`, `assert`, `mark`, `enforce`, `sanitize`, `normalize`, `compute`,
  `transform`, `parse`.

When in doubt, prefer the domain operation name. `Password::hash()` beats `Password::compute()`; `Email::parse()`
is fine in a parser library but suspicious elsewhere (use `Email::from()` instead).

### Architectural roles (allowed with justification)

These names describe a role the library offers as a building block. Acceptable when the class **is** that role
(e.g., `EventHandler` in an events library, `CacheManager` in a cache library, `Upcaster` in an event-sourcing
library). Not acceptable on domain objects inside the library (value objects, enums, contract interfaces):

- `Manager`, `Handler`, `Processor`, `Service`, and their verb forms `process`, `handle`, `execute`.

The test: if the consumer instantiates or extends this class to integrate with the library, the role name is
legitimate. If the class models a concept the consumer manipulates (a money amount, a country code, a color),
the role name is wrong.

## Value objects

1. Are immutable: no setters, no mutation after construction. Operations return new instances.
2. Compare by value, not by reference.
3. Validate invariants in the constructor and throw on invalid input.
4. Have no identity field.
5. Use static factory methods (e.g., `from`, `of`, `zero`) with a private constructor when multiple creation paths
   exist. The factory name communicates the semantic intent.

## Exceptions

1. Every failure throws a **dedicated exception class** named after the invariant it guards — never
   `throw new DomainException('...')`, `throw new InvalidArgumentException('...')`,
   `throw new RuntimeException('...')`, or any other generic native exception thrown directly. If the invariant
   is worth throwing for, it is worth a named class.
2. Dedicated exception classes **extend** the appropriate native PHP exception (`DomainException`,
   `InvalidArgumentException`, `OverflowException`, etc.) — the native class is the parent, never the thing that
   is thrown. Consumers that catch the broad standard types continue to work; consumers that need precise handling
   can catch the specific classes.
3. Exceptions are pure: no transport-specific fields (`code` populated with HTTP status, formatted `message` meant
   for end-user display). Formatting to any transport happens at the consumer's boundary, not inside the library.
4. Exceptions signal invariant violations only, not control flow.
5. Name the class after the invariant violated, never after the technical type:
    - `PrecisionOutOfRange` — not `InvalidPrecisionException`.
    - `CurrencyMismatch` — not `BadCurrencyException`.
    - `ContainerWaitTimeout` — not `TimeoutException`.
6. A descriptive `message` argument is allowed and encouraged when it carries **debugging context** — the violating
   value, the boundary that was crossed, the state the library was in. The class name identifies the invariant;
   the message describes the specific violation for stack traces and test assertions. Do not build messages meant
   for end-user display or transport rendering. Keep them short, factual, and in American English.
7. Public exceptions live in `src/Exceptions/`. Internal exceptions live in `src/Internal/Exceptions/`.

**Prohibited** — throwing a native exception directly:

```php
if ($value < 0) {
    throw new InvalidArgumentException('Precision cannot be negative.');
}
```

**Correct** — dedicated class, no message (class name is sufficient):

```php
// src/Exceptions/PrecisionOutOfRange.php
final class PrecisionOutOfRange extends InvalidArgumentException
{
}

// at the callsite
if ($value < 0) {
    throw new PrecisionOutOfRange();
}
```

**Correct** — dedicated class with debugging context:

```php
if ($value < 0 || $value > 16) {
    throw new PrecisionOutOfRange(sprintf('Precision must be between 0 and 16, got %d.', $value));
}
```

## Enums

1. Are PHP backed enums.
2. Include methods when they carry vocabulary meaning (e.g., `Order::ASCENDING_KEY`, `RoundingMode::apply()`).
3. Live at the `src/` root when public. Enums used only by internals live in `src/Internal/`.

## Extension points

1. When a class is designed to be extended by consumers (e.g., `Collection`, `ValueObject`), it uses `class` instead
   of `final readonly class`. All other classes use `final readonly class`.
2. Extension point classes use a private constructor with static factory methods (`createFrom`, `createFromEmpty`)
   as the only creation path.
3. Internal state is injected via the constructor and stored in a `private readonly` property.

## Time and space complexity

1. Every public method has predictable, documented complexity. Document Big O in PHPDoc on the interface
   (see `php-library-code-style.md`, "PHPDoc" section).
2. Algorithms run in `O(N)` or `O(N log N)` unless the problem inherently requires worse. `O(N²)` or worse must
   be justified and documented.
3. Prefer lazy/streaming evaluation over materializing intermediate results. In pipeline-style libraries, fuse
   stages so a single pass suffices.
4. Memory usage is bounded and proportional to the output, not to the sum of intermediate stages.
5. Validate complexity claims with benchmarks against a reference implementation when optimizing critical paths.
   Parity testing against the reference library is the validation standard for optimization work.
