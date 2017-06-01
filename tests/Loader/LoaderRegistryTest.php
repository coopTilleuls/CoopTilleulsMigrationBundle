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

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use CoopTilleuls\MigrationBundle\Loader\LoaderRegistry;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class LoaderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoaderRegistry
     */
    private $registry;

    /**
     * @var LoaderInterface|ObjectProphecy
     */
    private $loaderMock;

    protected function setUp()
    {
        $this->loaderMock = $this->prophesize(LoaderInterface::class);

        $this->registry = new LoaderRegistry([$this->loaderMock->reveal()]);
    }

    public function testGetLoaders()
    {
        $this->assertEquals([$this->loaderMock->reveal()], $this->registry->getLoaders());
    }

    public function testGetLoaderByName()
    {
        $this->loaderMock->getName()->willReturn('foo')->shouldBeCalledTimes(1);
        $this->assertEquals($this->loaderMock->reveal(), $this->registry->getLoaderByName('foo'));
    }

    /**
     * @expectedException \CoopTilleuls\MigrationBundle\Exception\LoaderNotFoundException
     * @expectedExceptionMessage Cannot find loader "bar".
     */
    public function testGetLoaderByNameException()
    {
        $this->loaderMock->getName()->willReturn('foo')->shouldBeCalledTimes(1);
        $this->registry->getLoaderByName('bar');
    }
}
