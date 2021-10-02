# laravel-repository-ide-helper

## Installation

```bash
composer require mingburnu/laravel-repository-ide-helper --dev
```

Register for Lumen, add Command to `app/Console/Kernel.php`

```php
protected $commands = [
    // ...
    \Mingburnu\RepositoryIdeHelper\Commands\GenerateCommand::class,
    // ...
];
```

## Usage

```bash
php artisan ide-helper:repository
```
