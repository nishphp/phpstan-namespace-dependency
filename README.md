# PHPStan Namespace Dependency Rule Extension

This package is a PHPStan extension for checking namespace dependencies.



## Install

```
composer require --dev nish/phpstan-namespace-dependency
```



## How to use

Add to `phpstan.neon`

```yaml
includes:
  - vendor/nish/phpstan-namespace-dependency/rules.neon

services:
  -
    factory: Nish\PHPStan\NsDepends\DependencyChecker([
      'callee class name prefix': ['caller class name prefix'],
    ], [
      'caller class name prefix': ['callee class name prefix'],
    ])
```



## Examples

### Layered

```yaml
includes:
  - vendor/nish/phpstan-namespace-dependency/rules.neon

services:
  -
    factory: Nish\PHPStan\NsDepends\DependencyChecker([
      'PDO': ['App\Dao'],
      'App\Dao': ['App\Model'],
      'App\Model': ['App\Page'],
    ], [])
```



### MVC

```yaml
includes:
  - vendor/nish/phpstan-namespace-dependency/rules.neon

services:
  -
    factory: Nish\PHPStan\NsDepends\DependencyChecker([
      'App\Model': ['App\Controller'],
      'App\View': ['App\Controller'],
    ], [
      'App\Model': ['App\Util', 'App\ValueObject'],
    ])
```



### DDD

```yaml
includes:
  - vendor/nish/phpstan-namespace-dependency/rules.neon

services:
  -
    factory: Nish\PHPStan\NsDepends\DependencyChecker([
      'App\DomainService': ['App\ApplicationService'],
      'App\Domain': ['App\DomainService', 'App\ApplicationService', 'App\Infrastructure'],
      'App\ApplicationService': ['App\Presentation', 'App\Tests'],
    ], [
      'App\Domain\DomainException': ['Exception'],
      'App\Domain': ['DateTimeInterface', 'DateTimeImmutable'],
      'App\DomainService': [],
      'App\ApplicationService': [],
    ])
```

