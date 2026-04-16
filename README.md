# Laravel Service Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zobirofkir/laravel-service-generator.svg)](https://packagist.org/packages/zobirofkir/laravel-service-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/zobirofkir/laravel-service-generator.svg)](https://packagist.org/packages/zobirofkir/laravel-service-generator)
[![License](https://img.shields.io/packagist/l/zobirofkir/laravel-service-generator.svg)](https://packagist.org/packages/zobirofkir/laravel-service-generator)
[![PHP Version Require](https://img.shields.io/packagist/php-v/zobirofkir/laravel-service-generator)](https://packagist.org/packages/zobirofkir/laravel-service-generator)
[![Laravel Version](https://img.shields.io/badge/Laravel-9.x%E2%80%9313.x-FF2D20)](https://laravel.com)

One-command generator for Laravel service architecture — creates Service classes, Contracts (interfaces), Facades, and Service Providers following SOLID principles and Laravel conventions.

## Table of contents

- Features
- Requirements
- Installation
- Usage
- Generated structure
- Architecture & patterns
- Examples
- Configuration
- Troubleshooting
- Contributing
- Changelog
- Security
- License
- Author
- Acknowledgments

## What it does in 5 seconds

Generates a Service class, its Contract (interface), a Facade and a Service Provider with one artisan command, wiring them for use in your Laravel app.

## Features

- One-command scaffold: Service, Contract, Facade, Provider
- Encourages SOLID design and testable code
- PSR-4 compatible and follows Laravel conventions
- Minimal setup with optional customization points

## Requirements

- PHP: 8.1+
- Laravel: 9.x — 13.x (tested)
- illuminate/support: compatible with Laravel 9 — 13

Note: These constraints reflect the versions this package is tested against. If you need support for other Laravel versions, please open an issue or PR.

## Installation

1. Install via Composer (recommended):

```bash
composer require zobirofkir/laravel-service-generator
```

2. Service provider

Laravel supports package auto-discovery. Manual registration is not required for recent Laravel versions. To register manually add the provider to `config/app.php`:

```php
'providers' => [
    Zobirofkir\\ServiceGenerator\\ServiceGeneratorServiceProvider::class,
];
```

3. Generate a service

```bash
php artisan make:service {ServiceName}
```

Examples:

```bash
# Generate a User service
php artisan make:service User

# Generate a Payment service
php artisan make:service Payment
```

What the command does

- Creates directories (if missing):
  - `app/Services/Services`
  - `app/Services/Constructors`
  - `app/Services/Facades`
  - `app/Providers`
- Generates four files for the given name: Service implementation, Contract (interface), Facade and Service Provider.
- Prints a success message when finished.

## Generated structure

After running `php artisan make:service User` the package creates:

```
app/
├── Services/
│   ├── Services/UserService.php
│   ├── Constructors/UserConstructor.php
│   └── Facades/UserFacade.php
└── Providers/UserServiceProvider.php
```

Example file snippets (simplified):

```php
// app/Services/Services/UserService.php
namespace App\\Services\\Services;

use App\\Services\\Constructors\\UserConstructor;

class UserService implements UserConstructor
{
    // implementation
}
```

```php
// app/Services/Constructors/UserConstructor.php
namespace App\\Services\\Constructors;

interface UserConstructor
{
    // contract methods
}
```

```php
// app/Services/Facades/UserFacade.php
namespace App\\Services\\Facades;

use Illuminate\\Support\\Facades\\Facade;

class UserFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'UserService';
    }
}
```

The generated `UserServiceProvider` binds the contract to the implementation and registers a singleton alias for the facade accessor.

## Architecture & patterns

The package scaffolds a small, opinionated layered architecture that helps keep your application maintainable and testable.

Typical flow:

Controller → Facade → Contract (interface) → Service implementation → Database / external APIs

Benefits:

- Loose coupling: controllers depend on interfaces, not concrete implementations.
- Testability: services and facades are easy to mock.
- Flexibility: swap implementations without changing consumers.

Design patterns used (brief):

- Facade — provides a simple static interface for services.
- Dependency Inversion / Contract pattern — services implement interfaces used by controllers.
- Service Provider — registers bindings in the container.
- Strategy (optional) — swap service implementations for different behaviors.

## Examples

Below are condensed examples to help you get started quickly.

Example: User service (contract + implementation + controller usage)

```php
// app/Services/Constructors/UserConstructor.php
namespace App\\Services\\Constructors;

use App\\Models\\User;
use Illuminate\\Database\\Eloquent\\Collection;

interface UserConstructor
{
    public function getAllUsers(): Collection;
    public function createUser(array $data): User;
    public function updateUser(int $id, array $data): User;
    public function deleteUser(int $id): bool;
    public function findByEmail(string $email): ?User;
}
```

```php
// app/Services/Services/UserService.php
namespace App\\Services\\Services;

use App\\Models\\User;
use App\\Services\\Constructors\\UserConstructor;
use Illuminate\\Database\\Eloquent\\Collection;
use Illuminate\\Support\\Facades\\Hash;

class UserService implements UserConstructor
{
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function updateUser(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return $user->fresh();
    }

    public function deleteUser(int $id): bool
    {
        return User::findOrFail($id)->delete();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
```

```php
// app/Http/Controllers/UserController.php
namespace App\\Http\\Controllers;

use App\\Services\\Facades\\UserFacade;
use App\\Http\\Requests\\UserRequest;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(UserFacade::getAllUsers());
    }

    public function store(UserRequest $request)
    {
        return response()->json(UserFacade::createUser($request->validated()), 201);
    }

    public function update(UserRequest $request, int $id)
    {
        return response()->json(UserFacade::updateUser($id, $request->validated()));
    }

    public function destroy(int $id)
    {
        UserFacade::deleteUser($id);
        return response()->json(null, 204);
    }
}
```

Other examples (payment, email) follow the same pattern: define the contract under `app/Services/Constructors`, implement it in `app/Services/Services`, and optionally add a facade and provider.

## Configuration

The package works out of the box and requires no configuration. Future releases may provide a publishable config (`php artisan vendor:publish`) to customize paths and namespaces.

If you need custom namespaces, extend the `MakeServiceCommand` and override path helpers to point to your preferred directories.

## Troubleshooting

- Class not found after generation: run `composer dump-autoload`.
- Facade not resolving: either register an alias in `config/app.php` or ensure the provider is registered.
- Provider not registered: add the generated provider to `config/app.php` providers array.
- Permission errors creating directories: adjust filesystem permissions (for example `chmod -R 775 app/Services`).

## Contributing

Contributions, bug reports and PRs are welcome. Please:

1. Fork the repository
2. Create a feature branch (git checkout -b feature/awesome)
3. Commit and push your changes
4. Open a pull request with a clear description

Local development

```bash
git clone https://github.com/zobirofkir/laravel-service-generator.git
cd laravel-service-generator
composer install
```

## Changelog

See `CHANGELOG.md` for a full history. Notable items:

- v1.0.0 — Initial release: service, contract, facade and provider generation; support for Laravel 9.x–13.x.

## Security

If you discover a security issue, please email Add your email here instead of opening a public issue.

## License

This package is open-sourced under the MIT license. See the `LICENSE` file for details.

## Author

Zobir Ofkir — GitHub: @zobirofkir

## Acknowledgments

- Laravel community
- Project contributors and users

Built with ❤️ for the Laravel community
