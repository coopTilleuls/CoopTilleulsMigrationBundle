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

namespace CoopTilleuls\MigrationBundle;

use CoopTilleuls\MigrationBundle\DependencyInjection\MigrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class CoopTilleulsMigrationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MigrationCompilerPass('coop_tilleuls_migration.loader', 'coop_tilleuls_migration.loader.locator', true));
        $container->addCompilerPass(new MigrationCompilerPass('coop_tilleuls_migration.transformer', 'coop_tilleuls_migration.transformer.locator'));
    }
}
