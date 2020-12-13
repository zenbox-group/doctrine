<?php

namespace ZenBox\Doctrine;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

final class FixturesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private string $fixturesPath;

    public function __construct(EntityManagerInterface $entityManager, string $fixturesPath)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->fixturesPath = $fixturesPath;
    }

    protected function configure()
    {
        $this
            ->setName('fixtures:load')
            ->setDescription('Load data fixtures to your database')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
            ->addOption('purge-with-truncate', null, InputOption::VALUE_NONE, 'Purge data by using a database-level TRUNCATE statement')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command loads data fixtures from your application:
  <info>%command.full_name%</info>
If you want to append the fixtures instead of flushing the database first you can use the <comment>--append</comment> option:
  <info>%command.full_name%</info> <comment>--append</comment>
By default Doctrine Data Fixtures uses DELETE statements to drop the existing rows from the database.
If you want to use a TRUNCATE statement instead you can use the <comment>--purge-with-truncate</comment> flag:
  <info>%command.full_name%</info> <comment>--purge-with-truncate</comment>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeader($output);

        if (!$input->getOption('append')) {
            $canContinue = $this->getHelper('question')->ask($input, $output, new ConfirmationQuestion(
                'Careful, database will be purged. Do you want to continue y/N ?', false
            ));
            if (!$canContinue) {
                $this->outputError($output);
                return self::FAILURE;
            }
        }

        $loader = new Loader();
        $loader->loadFromDirectory($this->fixturesPath);
        $purger = new ORMPurger();
        $purger->setPurgeMode($input->getOption('purge-with-truncate') ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->setLogger(function ($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($loader->getFixtures(), $input->getOption('append'));

        return self::SUCCESS;
    }

    protected function outputHeader(OutputInterface $output)
    {
        $name = 'Doctrine Database Fixtures';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('<question>' . $name . '</question>');
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('');
    }

    protected function outputError(OutputInterface $output)
    {
        $name = 'Doctrine Database Fixtures Cancelled';
        $name = str_repeat(' ', 15) . $name . str_repeat(' ', 15);
        $output->writeln('<error>' . str_repeat(' ', strlen($name)) . '</error>');
        $output->writeln('<error>' . $name . '</error>');
        $output->writeln('<error>' . str_repeat(' ', strlen($name)) . '</error>');
        $output->writeln('');
    }
}
