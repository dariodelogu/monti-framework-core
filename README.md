# Monti Framework Core

`dariodelogu/monti-core` provides the foundation of the [Monti framework](https://www.montiphpframework.com).

## Installation

The package is published on [Packagist](https://packagist.org/packages/dariodelogu/monti-core), so a Monti project only needs to require it:

```bash
composer require dariodelogu/monti-core
```

which adds it to `composer.json`:

```json
"require": {
    "dariodelogu/monti-core": "^1.0"
}
```

## Usage

```php
include(__DIR__ . "/../vendor/autoload.php");

\App\System\Project::setRootPath(__DIR__ . "/..");
\App\System\Project::init();
```

## License

Distributed under the Apache License, Version 2.0, same as the rest of the Monti framework.