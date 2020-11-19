# ZenBox Doctrine

[![PHP Version](https://img.shields.io/packagist/php-v/zenbox/doctrine.svg?style=for-the-badge)](https://packagist.org/packages/zenbox/doctrine)
[![Stable Version](https://img.shields.io/packagist/v/zenbox/doctrine.svg?style=for-the-badge&label=Latest)](https://packagist.org/packages/zenbox/doctrine)
[![Total Downloads](https://img.shields.io/packagist/dt/zenbox/doctrine.svg?style=for-the-badge&label=Total+downloads)](https://packagist.org/packages/zenbox/doctrine)

## Installation

Using Composer:

```sh
composer require zenbox/doctrine
```

## What is contained in the package?

### Doctrine components
- [ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/index.html)
- [Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.0/index.html)
- [Fixtures](https://github.com/doctrine/data-fixtures)

### QueryBuilderCollection

Lazy load query builder collection

```php
$qb = $this->createQueryBuilder('a');
$qb->orderBy('a.date','DESC');
$collection = new QueryBuilderCollection($qb);

$collection->count(); // returns the total number of records
$collection->slice(0, 10); // request limited 10 records from the database
```

### DataProvider

Can be used for pagination. 20 records per page by default

```php
use Doctrine\Common\Collections\ArrayCollection;$dataProvider = new DataProvider(new ArrayCollection([...]));

// iterable
foreach ($dataProvider as $object) {
    // do something
}

$array = $dataProvider->toArray(); // returns 20 records
```

Extract data from objects

```php
// implement extractor
use ZenBox\Doctrine\Extractor\ExtractorInterface;class UserExtractor implements ExtractorInterface
{
    public function extract(object $object) : array
    {
        // TODO: Implement extract() method.
    }
}
// fetch collection from repository
$collection = $repository->findAll();
$dataProvider = new DataProvider($collection, new UserExtractor());

// iterable
foreach ($dataProvider as $row) {
    // do something
}

$array = $dataProvider->extract(); // returns 20 rows
```

### Console commands

You need to create a file `./bin/doctrine` in your project for use console commands
```php
#!/usr/bin/env php
<?php

require __DIR__ . ' /../vendor/autoload.php';

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Psr\Container\ContainerInterface;use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Symfony\Component\Console\Helper\QuestionHelper;
use ZenBox\Doctrine\FixturesCommand;

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
$entityManager = $container->get(EntityManagerInterface::class);

$console = ConsoleRunner::createApplication(new HelperSet(
    [
        'db' => new ConnectionHelper($entityManager->getConnection()),
        'em' => new EntityManagerHelper($entityManager),
        'question' => new QuestionHelper(),
    ]
));

$dependencyFactory = DependencyFactory::fromConnection(
        new ConfigurationArray([
            'migrations_paths' => [
                'MyProject\Migrations' => realpath(__DIR__ . '/../data/migrations/MyProject/Migrations'),
            ]
        ]), new ExistingConnection($entityManager->getConnection()));
MigrationsConsoleRunner::addCommands($console, $dependencyFactory);

$console->add(new FixturesCommand($entityManager, realpath(__DIR__ . '/../src')));

$console->run();
```

### Container configuration

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
                        // iterate through all folders to find metadata configuration
                        (new DirectoryPathIterator(realpath(__DIR__) . '/../config/doctrine'))->toArray(),
                        getenv('APP_DEV_MODE')
                    )
                );
            },
            // you can use definition factory
            UserRepositoryInterface::class => DefinitionFactory::create(User::class),
            // instead
            UserRepositoryInterface::class => function (ContainerInterface $container) {
               return $container->get(EntityManagerInterface::class)->getRepository(User::class);
            }
        ],
    ],
];
```
