<?php

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
     * Get loader name.
     *
     * @return string
     */
    public function getName();

    /**
     * Execute loader.
     */
    public function execute();

    /**
     * Get the number of rows imported.
     *
     * @return int
     */
    public function getNbRows();
}
