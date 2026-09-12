# SECURITY — Rules, Auth, Validation

This file defines security rules, authentication/authorization patterns, and validation requirements. Rules are universal unless marked project-specific where security patterns diverge.

---

## Authentication

- Laravel Breeze + Inertia (Vue) — routes in `routes/auth.php`, controllers in `app/Http/Controllers/Auth/`, pages in `resources/js/pages/Auth/`.
- Session-based authentication.
- Email verification required for application access (`verified` middleware on protected application routes).
- Password confirmation for sensitive actions (`password.confirm` route).

---

## Authorization

### Middleware
- `auth` middleware for protected routes.
- `verified` middleware for email verification.
- `guest` middleware for public-only routes.
- `signed` middleware for email verification links.
- `throttle` middleware for rate limiting.

### Policy-Based
- Policies exist for model-level authorization.
- New features MUST implement actual authorization logic.
- Do not ship placeholder policies that return `false` for everything.

### Role-Based (Spatie Permission)
- `User` model uses `HasRoles` / `HasPermissions` (backed by `App\Contracts\Model\HasRolesContract` / `HasPermissionsContract`).
- Enforce permissions server-side via middleware gates or `can:`/policies — never hide UI alone.
- Audit role/permission changes (records via `spatie/laravel-activitylog`).

---

## Validation

### Primary: In Actions
RuledActions validate data before business logic:

```php
public function rules(array $payload): array
{
    return [
        'nik' => ['required', 'string', 'max:16', Rule::unique(SidResident::class)],
        'name' => ['required', 'string', 'max:255'],
    ];
}
```

### Secondary: In Controllers
Simple inline validation for basic cases:

```php
$validatedData = $request->validate([
    'nik' => 'required|unique:residents|max:255',
    'nama_lengkap' => 'required|max:255',
]);
```

### Form Requests
Used for authentication flows and settings (complex request-level validation).
- Business logic validation belongs in RuledActions, not Form Requests.

---

## Rate Limiting

Implement rate limiting for sensitive endpoints:
```php
if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
    throw ValidationException::withMessages([...]);
}
```

And in routes:
```php
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1']);
```

---

## Sensitive Data Handling

### Indonesian Personal Data (NIK / KK / Addresses)
The SID domain handles Indonesian personal data (`nik`, family/address records). These are sensitive:
- Never log, dump, or expose NIK/identity fields in responses, errors, or debug output.
- Omit personal identity fields from lists/datasets unless the feature truly needs them; prefer `select()` over `*` in repository queries that expose them.
- Mask or exclude identity attributes in audit/activity logs where feasible.

### Hidden Attributes
```php
protected $hidden = ['password', 'remember_token'];
```

### Media Uploads (Spatie MediaLibrary)
- Handle user-uploaded files through `spatie/laravel-medialibrary` on the model (conversions, validation) — never store raw uploaded paths manually.
- Validate upload MIME/size server-side; sanitize filenames.

### Audit Trails (Spatie ActivityLog)
- Important mutations (user, resident, group/menu settings) MUST be recorded via `activity()` / the model's `LogsActivity` trait so history is not lost.
- Never delete activity/history records.

### Telescope (dev validation)
Hide sensitive request details even when Telescope is enabled:
```php
Telescope::hideRequestParameters(['_token', 'nik']);
Telescope::hideRequestHeaders(['cookie', 'x-csrf-token', 'x-xsrf-token']);
```

### Password Hashing
- `password` cast as `hashed` in User model.
- `Hash::make()` used in password creation/reset.
- Configure appropriate BCRYPT_ROUNDS for production and testing.

---

## Session Security

```php
$request->session()->invalidate();
$request->session()->regenerateToken();
```
Used after logout and password changes.

---

## CSRF Protection

- Use Laravel's built-in CSRF token handling.
- Use `@routes` directive / Ziggy for route generation in JavaScript.
- Never disable CSRF middleware.

---

## Key Security Rules

1. **NEVER** store plaintext passwords.
2. **ALWAYS** use `Hash::make()` for password creation.
3. **ALWAYS** validate input at the Action layer.
4. **ALWAYS** use `auth` middleware for protected routes.
5. **ALWAYS** use `Rule::unique()` for unique validation (not raw SQL).
6. **ALWAYS** use `Rule::exists()` for foreign key validation.
7. **NEVER** expose sensitive data in responses.
8. **NEVER** trust user input — always validate.
9. Use `constrained()` on foreign key migrations.
10. **NEVER** commit secrets or API keys to the repository.

---

## Prohibited in Committed Code

- Using `dd()`, `dump()`, or `ray()` in committed code.
- Logging passwords, tokens, or sensitive data.
- Returning raw database errors to users.
- Storing secrets in plaintext config files committed to git.
