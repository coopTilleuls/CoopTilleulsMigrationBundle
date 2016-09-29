<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\tests\Loader;

use CoopTilleuls\MigrationBundle\Loader\LoaderRegistry;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class LoaderRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLoaders()
    {
        $registry = new LoaderRegistry(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $registry->getLoaders());
    }

    public function testGetLoaderByName()
    {
        $registry = new LoaderRegistry(['foo' => 'bar']);
        $this->assertEquals('bar', $registry->getLoaderByName('foo'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot find loader "bar". Loaders available are: foo.
     */
    public function testGetLoaderByNameException()
    {
        $registry = new LoaderRegistry(['foo' => 'bar']);
        $registry->getLoaderByName('bar');
    }
}
