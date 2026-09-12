# FORBIDDEN BEHAVIOR — Explicit Prohibitions

The following behaviors are explicitly forbidden. These are absolute rules unless the user explicitly overrides them for a specific task.

---

## Architecture Violations

- ❌ Writing any code before reading the build specification (root `MASTER_BUILD_SPECIFICATION.md`), or before creating it via detailed operator Q&A when it does not exist.
- ❌ Guessing project specifications (feature names, columns, relations, conventions) that belong in `MASTER_BUILD_SPECIFICATION.md` instead of discussing with the operator.
- ❌ Putting business logic in Controllers.
- ❌ Accessing Eloquent directly from Actions or Controllers (must go through Repository).
- ❌ Creating generic catch-all `Service`/`Helper`/`Utils` classes or empty DTOs without a clear need (a domain-named **Service** layer is legitimate and allowed when warranted — see `03-architecture.md`; it is not forbidden, only gratuitous services are).
- ❌ Creating unnecessary abstractions (only create contracts when multiple implementations or clear need exists).
- ❌ Creating files outside the feature's context.
- ❌ Modifying routes outside the contextual naming convention.
- ❌ Violating dependency direction rules:
  - Model → Repository
  - Repository → Action
  - Action → Controller
  - Controller → Repository directly (must go through an Action)
  - Leaves calling Eloquent directly (Controller/Model → Model::query)
  - Circular dependencies
- ❌ Introducing circular dependencies.
- ❌ Reproducing legacy duplicate class names when a canonical contextual class exists (e.g. creating `CreateResidentAction` alongside `CreateSidResidentAction`).

---

## Style Violations

- ❌ Using PHPDoc `@test` annotations or omitting the `#[Test]` attribute on test methods (`it_…`/`user_can_…` names are fine **with** the attribute).
- ❌ Adding comments unless asked.
- ❌ Using `declare(strict_types=1)` (project convention).
- ❌ Using tabs for indentation (use 4 spaces).
- ❌ Using Options API in Vue (always Composition API).
- ❌ Skipping TypeScript types in frontend.
- ❌ Using generic/technical names for domain concepts (`TypeService`, `DataHandler`, `Manager`; `Helper.php`, `Utils.php`).

---

## Implementation Violations

- ❌ Modifying vendor code without confirmation.
- ❌ Adding dependencies without checking the existing codebase first.
- ❌ Refactoring working code (speculative refactoring).
- ❌ Fixing bugs not directly related to the current task.
- ❌ Doing work directly on `develop` or `main` (create a feature branch from `develop`).
- ❌ Creating empty commits.
- ❌ Pushing secrets or credentials.
- ❌ Using `dd()`, `dump()`, or `ray()` in committed code.
- ❌ Running the full test suite unless explicitly asked.
- ❌ Mocking in Feature tests.
- ❌ Calling an instance Action `handle()` statically (`XxxAction::handle(...)`) — resolve and call via the container on an instance.
- ❌ Calling `$action->execute(...)` — no such method exists on the Action abstraction (`handle()` is the only invocation API).
- ❌ Passing a second argument to `handle()` (`$action->handle($id, $request->all())`) — the extra argument is ignored and the call breaks the array-payload protocol.
- ❌ Passing a scalar payload to a **RuledAction** (`→handle($id)`) — ruled actions require an array payload or they throw `InvalidArgumentException('Payload must be an array.')`; put `'id'`/a Model inside the array instead (`→handle(['id' => $id])`).
- ❌ Reproducing any `// BAD` pattern from `12-project-specific/canonical-snippets.md` §11.
- ❌ Referencing classes/methods that do not exist (broken imports, undefined variables, unknown repository methods). Always verify the target class/method exists in the codebase before using it.
- ❌ Passing payload keys to Actions that do not match the repository/model columns or the RuledAction validation rules.

---

## Security Violations

- ❌ Storing plaintext passwords.
- ❌ Exposing sensitive data in responses.
- ❌ Trusting user input without validation.
- ❌ Committing secrets or API keys.
- ❌ Logging passwords, tokens, or sensitive data.
- ❌ Returning raw database errors to users.

---

## Scope Violations

- ❌ Modifying unrelated files.
- ❌ Making speculative changes.
- ❌ Fixing unrelated issues while implementing a feature.
- ❌ Creating controllers or frontend components unless explicitly asked (feature stops at Action layer by default).

---

## Unknown Territory Handling

When you encounter a situation not covered by these instructions:
1. State the problem clearly.
2. Propose the minimal fix.
3. Get user confirmation before proceeding.
4. Do not invent new patterns without evidence from the codebase.
