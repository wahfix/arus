# TESTING — Strategy, Patterns, Conventions

This file defines testing conventions for PHPUnit/Laravel-based projects. Apply them where the technology matches.

---

## Framework & Configuration

- **PHPUnit** with Laravel test helpers.
- **SQLite in-memory** database for all tests.
- **Test suites:** `Unit` and `Feature` (defined in `phpunit.xml`).

---

## Test Attribute Convention

Use the `#[Test]` attribute on every test method:

```php
use PHPUnit\Framework\Attributes\Test;

class SomeTest extends TestCase
{
    #[Test]
    public function user_can_create_article(): void
    {
        // ...
    }
}
```

Method names are descriptive snake_case. All of these occur in the codebase — pick the pattern used by the neighbouring test file:

```php
#[Test]
public function user_can_view_articles_index(): void       // feature endpoints
#[Test]
public function it_creates_a_group_with_valid_data(): void // unit behavior
#[Test]
public function test_throws_validation_exception_for_invalid_data(): void // unit behavior
```

**Do not use PHPDoc `@test` annotations and do not rely on the bare `test_` prefix alone** — `#[Test]` (PascalCase attribute class) is the canonical marker. Never write a test method that claims `it_` intent while skipping the attribute.

---

## Test Naming Convention

- Test methods: descriptive snake_case (`user_can_…`, `it_…`, `test_…`) marked with `#[Test]`.
- Test classes: `{Entity}Test` for Unit, `{Feature}Test` for Feature.
- Method names describe the behavior being tested.

```php
// Good
public function user_can_view_articles_index()
public function it_creates_a_group_with_valid_data()
public function test_throws_validation_exception_for_invalid_data()

// Avoid
public function createGroup()
public function testGroupCreation()
```

---

## Test Organization

### Unit Tests (`tests/Unit/`)
- Model behavior tests.
- Action logic tests (with mocked repositories).
- Domain logic tests.
- Mirror source structure: `tests/Unit/Actions/Group/CreateGroupActionTest.php`, `tests/Unit/Actions/Web/Dashboard/Sidebar/GetAllSidebarMenuActionTest.php`.

### Feature Tests (`tests/Feature/`)
- HTTP endpoint tests.
- Full integration tests with database.
- Mirror route/feature structure:
  - `tests/Feature/Web/WebArticleTest.php`
  - `tests/Feature/Settings/ProfileUpdateTest.php`
  - `tests/Feature/Auth/RegistrationTest.php`

---

## Base TestCase

All test classes extend `Tests\TestCase`:

```php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```

---

## Common Traits

`RefreshDatabase` is used on virtually every test class:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class SomeTest extends TestCase
{
    use RefreshDatabase;
    // ...
}
```

---

## Test Data

### Factory Usage (preferred)
```php
$user = User::factory()->create();
$group = Group::factory()->create(['name' => 'Test Group']);
$menu = Menu::factory()->create();
```

### Direct Creation (Unit Tests)
```php
$group = Group::create([
    'name' => 'Test Group',
    'slug' => 'test-group',
    'description' => 'A test group',
]);
```

---

## Mocking Rules

- Use `$this->mock()` for Laravel container mocking.
- Use Mockery for manual mocking.
- Mock at Repository layer for Unit tests.
- **DO NOT mock in Feature tests** — use the real database with `RefreshDatabase`.

---

## Feature Test Pattern

```php
class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function user_can_view_articles_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.web.articles.index'))
            ->assertOk();
    }

    #[Test]
    public function user_can_create_article(): void
    {
        $this->actingAs($this->user)
            ->post(route('dashboard.web.articles.store'), [...])
            ->assertRedirect(route('dashboard.web.articles.index'));

        $this->assertDatabaseHas('web_articles', [...]);
    }
}
```

---

## Action Test Pattern (Unit)

```php
class CreateGroupActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(GroupRepository::class, $this->createMock(GroupRepository::class));
    }

    #[Test]
    public function it_creates_a_group_with_valid_data(): void
    {
        $data = [
            'name' => 'Test Group',
            'description' => 'Ea est dolor consequatur cum rerum.',
            'type' => 'test_type',
            'url' => 'http://example.com',
            'icon' => 'test_icon',
        ];

        $this->mock(GroupRepository::class, function ($mock) use ($data) {
            $mock->shouldReceive('store')
                ->once()
                ->andReturn(new Group($data));
        });

        $action = new CreateGroupAction($this->app->make(GroupRepository::class));
        $group = $action->handle($data);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('Test Group', $group->name);
    }
}
```

> Actions are invoked with `->handle($payload)`. The legacy `$action->execute($data)` form is a **broken reference to a non-existent method** — never use it (see `12-project-specific/canonical-snippets.md` §9).

---

## Assertion Patterns

```php
// Database assertions
$this->assertDatabaseHas('table', ['column' => 'value']);
$this->assertDatabaseMissing('table', ['id' => $id]);

// Response assertions
$response->assertOk();
$response->assertStatus(200);
$response->assertRedirect(route('name'));
$response->assertSessionHasNoErrors();

// Object assertions
$this->assertInstanceOf(Model::class, $result);
$this->assertEquals('expected', $result->name);
$this->assertNotNull($result);
$this->assertCount(2, $collection);
$this->assertTrue($collection->contains($item));
$this->assertFalse($collection->contains($item));

// Authentication
$this->actingAs($user);
$this->assertAuthenticated();
$this->assertGuest();

// Exception testing
$this->expectException(ValidationException::class);
$this->expectException(CircularMembershipException::class);
$this->expectExceptionMessage('Expected string groupKey');
```

---

## What Makes a "Good Test"

1. Uses `RefreshDatabase`.
2. Creates test data via factories or direct creation.
3. Tests behavior, not implementation.
4. Uses `#[Test]` attribute.
5. Feature tests use HTTP methods (get, post, put, delete).
6. Asserts database state changes.
7. Tests authorization (guest redirects, authenticated access).
8. Tests both happy path and error cases where applicable.

---

## When to Run Tests

- **Do NOT run the full test suite** unless explicitly asked (it slows down development).
- When tests ARE run, focus only on relevant tests:
  ```bash
  php artisan test --filter=ArticleTest
  ```
- If tests fail, fix ONLY the code related to the current feature.
- Do NOT fix unrelated test failures.
