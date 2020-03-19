<?php

/*
 * This file is part of the MigrationBundle.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Command;

use CoopTilleuls\MigrationBundle\Exception\LoaderNotFoundException;
use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class MigrationLoadCommand extends Command
{
    private $loaderLocator;
    private $style;

    public function __construct(ContainerInterface $loaderLocator)
    {
        parent::__construct('migration:load');

        $this->loaderLocator = $loaderLocator;

        $this
            ->setDescription('Import data from legacy to current database')
            ->addArgument('loader', InputArgument::REQUIRED, 'Name of the loader to execute')
            ->setHelp(<<<'EOT'
The <info>migration:load</info> command executes a loader to import data from legacy to current database:

<info>php bin/console migration:load {loader name}</info>
EOT
            );
    }

    public function setStyle(StyleInterface $style): void
    {
        $this->style = $style;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('loader');
        if (!$this->loaderLocator->has($name)) {
            throw new LoaderNotFoundException($name);
        }

        /** @var LoaderInterface $loader */
        $loader = $this->loaderLocator->get($name);

        /** @var StyleInterface $io */
        $io = $this->style ?: new SymfonyStyle($input, $output);
        $io->title(sprintf('Loading data from loader "%s"', $name));

        $loader->execute();

        if (0 === $loader->getNbRows()) {
            $io->text('No data loaded');
        } else {
            $io->text(sprintf('%d record(s) successfully loaded', $loader->getNbRows()));
        }

        $io->success(sprintf('Loader "%s" successfully executed', $name));

        return 0;
    }
}
