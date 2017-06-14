<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class FooLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRows()
    {
        return 3;
    }
}
