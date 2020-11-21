<?php

declare(strict_types=1);

namespace ZenBox\Doctrine\Console;

use Doctrine\DBAL\Tools\Console\Command as DBALConsole;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command as MigrationsConsole;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command as ORMConsole;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;
use ZenBox\Doctrine\FixturesCommand;

final class DoctrineConsoleRunner
{
    public static function run(EntityManagerInterface $entityManager, array $config): void
    {
        $cli = static::createApplication($entityManager, $config);
        $cli->run();
    }

    public static function createApplication(EntityManagerInterface $entityManager, array $config)
    {
        $cli = new Application('Doctrine Console');
        $cli->setCatchExceptions(true);
        $cli->setHelperSet(new HelperSet(
            [
                'db' => new ConnectionHelper($entityManager->getConnection()),
                'em' => new EntityManagerHelper($entityManager),
                'question' => new QuestionHelper(),
            ]
        ));

        try {
            self::addCommands($cli, $entityManager, $config);
        } catch (Throwable $e) {
            $output = new ConsoleOutput();
            $cli->renderThrowable($e, $output->getErrorOutput());
        }

        return $cli;
    }

    public static function addCommands(Application $cli, EntityManagerInterface $entityManager, array $config): void
    {
        if (empty($config['migrations'])) throw InvalidConfigurationKey::new('migrations');
        if (empty($config['fixtures_path'])) throw InvalidConfigurationKey::new('fixtures_path');

        $dependencyFactory = DependencyFactory::fromConnection(
            new ConfigurationArray($config['migrations']), new ExistingConnection($entityManager->getConnection()));

        $cli->addCommands([
            // DBAL Commands
            new DBALConsole\ReservedWordsCommand(),
            new DBALConsole\RunSqlCommand(),
            // ORM Commands
            new ORMConsole\ClearCache\CollectionRegionCommand(),
            new ORMConsole\ClearCache\EntityRegionCommand(),
            new ORMConsole\ClearCache\MetadataCommand(),
            new ORMConsole\ClearCache\QueryCommand(),
            new ORMConsole\ClearCache\QueryRegionCommand(),
            new ORMConsole\ClearCache\ResultCommand(),
            new ORMConsole\SchemaTool\CreateCommand(),
            new ORMConsole\SchemaTool\UpdateCommand(),
            new ORMConsole\SchemaTool\DropCommand(),
            new ORMConsole\EnsureProductionSettingsCommand(),
            new ORMConsole\GenerateProxiesCommand(),
            new ORMConsole\ConvertMappingCommand(),
            new ORMConsole\RunDqlCommand(),
            new ORMConsole\ValidateSchemaCommand(),
            new ORMConsole\InfoCommand(),
            new ORMConsole\MappingDescribeCommand(),
            // Migrations Commands
            new MigrationsConsole\CurrentCommand($dependencyFactory),
            new MigrationsConsole\DumpSchemaCommand($dependencyFactory),
            new MigrationsConsole\ExecuteCommand($dependencyFactory),
            new MigrationsConsole\GenerateCommand($dependencyFactory),
            new MigrationsConsole\LatestCommand($dependencyFactory),
            new MigrationsConsole\MigrateCommand($dependencyFactory),
            new MigrationsConsole\RollupCommand($dependencyFactory),
            new MigrationsConsole\StatusCommand($dependencyFactory),
            new MigrationsConsole\VersionCommand($dependencyFactory),
            new MigrationsConsole\UpToDateCommand($dependencyFactory),
            new MigrationsConsole\SyncMetadataCommand($dependencyFactory),
            new MigrationsConsole\ListCommand($dependencyFactory),
            new MigrationsConsole\DiffCommand($dependencyFactory),
            // Fixture Commands
            new FixturesCommand($entityManager, $config['fixtures_path']),
        ]);
    }
}
