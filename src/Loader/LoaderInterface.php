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

namespace CoopTilleuls\MigrationBundle\Loader;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface LoaderInterface
{
    /**
     * Execute loader.
     */
    public function execute(): void;

    /**
     * Get the number of rows imported.
     */
    public function getNbRows(): int;
}
