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

namespace CoopTilleuls\MigrationBundle\E2e\LegacyBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class FooLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRows(): int
    {
        return 3;
    }
}
