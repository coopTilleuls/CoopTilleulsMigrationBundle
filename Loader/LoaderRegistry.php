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

use CoopTilleuls\MigrationBundle\Exception\LoaderNotFoundException;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class LoaderRegistry
{
    /**
     * @var LoaderInterface[]
     */
    private $loaders = [];

    public function __construct(array $loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * @return LoaderInterface[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * @param string $name
     *
     * @return LoaderInterface
     *
     * @throws LoaderNotFoundException
     */
    public function getLoaderByName($name)
    {
        if (!array_key_exists($name, $this->loaders)) {
            throw new LoaderNotFoundException($name, array_keys($this->loaders));
        }

        return $this->loaders[$name];
    }
}
