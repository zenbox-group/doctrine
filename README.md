# ZenBox Doctrine

[![PHP Version](https://img.shields.io/packagist/php-v/zenbox/doctrine.svg?style=for-the-badge)](https://packagist.org/packages/zenbox/doctrine)
[![Stable Version](https://img.shields.io/packagist/v/zenbox/doctrine.svg?style=for-the-badge&label=Latest)](https://packagist.org/packages/zenbox/doctrine)
[![Total Downloads](https://img.shields.io/packagist/dt/zenbox/doctrine.svg?style=for-the-badge&label=Total+downloads)](https://packagist.org/packages/zenbox/doctrine)

Doctrine extensions

## Installation

Using Composer:

```sh
composer require zenbox/doctrine
```
## Examples

Console commands `./bin/doctrine`
```php
#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Symfony\Component\Console\Helper\QuestionHelper;
use ZenBox\Doctrine\FixturesCommand;

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
$entityManager = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

$config = new Configuration($entityManager->getConnection());
$config->setMigrationsDirectory(realpath(__DIR__ . '/../src/Migrations'));
$config->setMigrationsNamespace('App\Migrations');

$console = ConsoleRunner::createApplication(new HelperSet(
    [
        'db' => new ConnectionHelper($entityManager->getConnection()),
        'em' => new EntityManagerHelper($entityManager),
        'configuration' => new ConfigurationHelper($entityManager->getConnection(), $config),
        'question' => new QuestionHelper(),
    ]
));

MigrationsConsoleRunner::addCommands($console);

$console->add(new FixturesCommand($entityManager, realpath(__DIR__ . '/../src')));

$console->run();
```

Container configuration
```php
<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use ZenBox\Doctrine\DefinitionFactory;
use ZenBox\Doctrine\Iterator\DirectoryPathIterator;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;

return [
    'dependencies' => [
        'factories'  => [
            EntityManagerInterface::class => function() {

                return EntityManager::create(
                    [
                        'dbname' => getenv('MYSQL_DATABASE'),
                        'user' => getenv('MYSQL_USER'),
                        'password' => getenv('MYSQL_PASSWORD'),
                        'host' => getenv('MYSQL_HOST'),
                        'driver' => 'pdo_mysql',
                    ],
                    Setup::createYAMLMetadataConfiguration(
                        (new DirectoryPathIterator(realpath(__DIR__) . '/doctrine'))->toArray(),
                        true
                    )
                );
            },
            UserRepositoryInterface::class => DefinitionFactory::create(User::class),
        ],
    ],
];
```
