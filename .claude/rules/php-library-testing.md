---
description: BDD Given/When/Then structure, PHPUnit conventions, test organization, and fixture rules for PHP libraries.
paths:
    - "tests/**/*.php"
---

# Testing conventions

Framework: **PHPUnit**. Refer to `php-library-code-style.md` for the code style checklist, which also applies to
test files.

## Structure: Given/When/Then (BDD)

Every test uses `/** @Given */`, `/** @And */`, `/** @When */`, `/** @Then */` doc comments without exception.

### Happy path example

```php
public function testAddMoneyWhenSameCurrencyThenAmountsAreSummed(): void
{
    /** @Given two money instances in the same currency */
    $ten = Money::of(amount: 1000, currency: Currency::BRL);
    $five = Money::of(amount: 500, currency: Currency::BRL);

    /** @When adding them together */
    $total = $ten->add(other: $five);

    /** @Then the result contains the sum of both amounts */
    self::assertEquals(expected: 1500, actual: $total->amount());
}
```

### Exception example

When testing that an exception is thrown, place `@Then` (expectException) **before** `@When`. PHPUnit requires this
ordering.

```php
public function testAddMoneyWhenDifferentCurrenciesThenCurrencyMismatch(): void
{
    /** @Given two money instances in different currencies */
    $brl = Money::of(amount: 1000, currency: Currency::BRL);
    $usd = Money::of(amount: 500, currency: Currency::USD);

    /** @Then an exception indicating currency mismatch should be thrown */
    $this->expectException(CurrencyMismatch::class);

    /** @When trying to add money with different currencies */
    $brl->add(other: $usd);
}
```

Use `@And` for complementary preconditions or actions within the same scenario, avoiding consecutive `@Given` or
`@When` tags.

## Rules

1. Include exactly one `@When` per test. Two actions require two tests.
2. Test only the public API. Never assert on private state or `Internal/` classes directly.
3. Never mock internal collaborators. Use real objects. Use test doubles only at system boundaries (filesystem,
   clock, network) when the library interacts with external resources.
4. Name tests to describe behavior, not method names.
5. Never include conditional logic inside tests.
6. Include one logical concept per `@Then` block.
7. Maintain strict independence between tests. No inherited state.
8. Use domain-specific model classes in `tests/Models/` for test fixtures that represent domain concepts
   (e.g., `Amount`, `Invoice`, `Order`).
9. Use mock classes in `tests/Mocks/` (or `tests/Unit/Mocks/`) for test doubles of system boundaries
   (e.g., `ClientMock`, `ExecutionCompletedMock`).
10. Exercise invariants and edge cases through the library's public entry point. Create a dedicated test class
    for an internal model only when the condition cannot be reached through the public API.
11. Never use `/** @test */` annotation. Test methods are discovered by the `test` prefix in the method name.
12. Never use named arguments on PHPUnit assertions (`assertEquals`, `assertSame`, `assertTrue`,
    `expectException`, etc.). Pass arguments positionally.

## Test setup and fixtures

1. **One annotation = one statement.** Each `@Given` or `@And` block contains exactly one annotation line
   followed by one expression or assignment. Never place multiple variable declarations or object
   constructions under a single annotation.
2. **No intermediate variables used only once.** If a value is consumed in a single place, inline it at the
   call site. Chain method calls when the intermediate state is not referenced elsewhere
   (e.g., `Money::of(...)->add(...)` instead of `$money = Money::of(...); $money->add(...);`).
3. **No private or helper methods in test classes.** The only non-test methods allowed are data providers.
   If setup logic is complex enough to extract, it belongs in a dedicated fixture class, not in a
   private method on the test class.
4. **Domain terms in variables and annotations.** Never use technical testing jargon (`$spy`, `$mock`,
   `$stub`, `$fake`, `$dummy`) as variable or property names. Use the domain concept the object
   represents: `$collection`, `$amount`, `$currency`, `$sortedElements`. Class names like
   `ClientMock` or `GatewaySpy` are acceptable — the variable holding the instance is what matters.
5. **Annotations use domain language.** Write `/** @Given a collection of amounts */`, not
   `/** @Given a mocked collection in test state */`. The annotation describes the domain
   scenario, not the technical setup.

## Test organization

```
tests/
├── Models/         # Domain-specific fixtures reused across tests
├── Mocks/          # Test doubles for system boundaries
├── Unit/           # Unit tests for public API
│   └── Mocks/      # Alternative location for test doubles
├── Integration/    # Tests requiring real external resources (Docker, filesystem)
└── bootstrap.php   # Test bootstrap when needed
```

`tests/Integration/` is only present when the library interacts with infrastructure.

## Coverage and mutation testing

1. Line and branch coverage must be **100%**. No annotations (`@codeCoverageIgnore`), attributes, or configuration
   that exclude code from coverage are allowed.
2. All mutations reported by Infection must be **killed**. Never ignore or suppress mutants via `infection.json.dist`
   or any other mechanism.
3. If a line or mutation cannot be covered or killed, it signals a design problem in the production code. Refactor
   the code to make it testable, do not work around the tool.
