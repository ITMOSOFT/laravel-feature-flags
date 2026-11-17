# ITMOSOFT Laravel Feature Flags

Database-backed feature flags for Laravel 12 and 13, with deterministic percentage rollouts and per-model overrides.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-feature-flags
php artisan vendor:publish --tag=itmosoft-feature-flags-config
php artisan migrate
```

The service provider is auto-discovered by Laravel.

## Quick start

```php
use ItmoSoft\FeatureFlags\Facades\FeatureFlags;

// Create or update a globally enabled flag.
FeatureFlags::define('new-dashboard', enabled: true);

// Check a flag.
if (FeatureFlags::enabled('new-dashboard')) {
    // Show the new dashboard.
}
```

Use a deterministic percentage rollout:

```php
FeatureFlags::define('checkout-v2', enabled: true, rolloutPercentage: 25);

if (FeatureFlags::enabled('checkout-v2', auth()->user())) {
    // Approximately 25% of distinct users receive the feature.
}
```

A subject-specific override takes precedence over the global flag and rollout:

```php
FeatureFlags::forSubject('checkout-v2', $user, true);
FeatureFlags::enabled('checkout-v2', $user); // true

FeatureFlags::forSubject('checkout-v2', $user, false);
FeatureFlags::enabled('checkout-v2', $user); // false
```

Remove an override:

```php
FeatureFlags::forgetSubject('checkout-v2', $user);
```

## API

| Method | Description |
| --- | --- |
| `define(string $key, bool $enabled = false, int $rolloutPercentage = 100)` | Create or update a flag |
| `enabled(string $key, ?Model $subject = null): bool` | Evaluate a flag |
| `forSubject(string $key, Model $subject, bool $enabled): void` | Set a subject override |
| `forgetSubject(string $key, Model $subject): void` | Remove a subject override |
| `find(string $key): ?FeatureFlag` | Retrieve a flag model |
| `delete(string $key): void` | Delete a flag and its overrides |

### Evaluation rules

1. A missing flag evaluates to `false`.
2. If a subject-specific override exists, it wins.
3. If the flag is globally disabled, it evaluates to `false`.
4. If no subject is supplied, a globally enabled flag evaluates to `true`; rollout percentages are only evaluated when a subject is supplied.
5. With a subject, a stable SHA-256 bucket based on the flag key and subject morph identity is compared with the rollout percentage.

The rollout is deterministic for a given flag key and subject identity. It is not a random sampling mechanism and does not guarantee an exact percentage for small populations.

## Configuration

Publish `config/feature-flags.php` to customize the table names:

```php
return [
    'tables' => [
        'flags' => 'feature_flags',
        'assignments' => 'feature_flag_assignments',
    ],
];
```

## Testing

```bash
composer install
composer test
```

## Scope and limitations

- Uses the application's configured database connection.
- Subject overrides use Laravel polymorphic model identities (`getMorphClass()` and model key).
- No admin UI, HTTP endpoints, dashboard, remote configuration service, scheduled rollout windows, user-segment query language, or distributed cache layer.
- Percentage rollout is evaluated in-process and is stable, but not cryptographically suitable for security or access-control decisions.
- Feature flags are not authorization. Continue enforcing permissions independently.

## License

MIT. See [LICENSE](LICENSE).
