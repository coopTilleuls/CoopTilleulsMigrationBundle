<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\tests\Transformer;

use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\Transformer\TransformerChain;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class TransformerChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy|TransformerInterface
     */
    private $transformerMock;

    /**
     * @var ObjectProphecy|TransformerEvent
     */
    private $eventMock;

    /**
     * @var TransformerChain
     */
    private $transformer;

    protected function setUp()
    {
        $this->transformerMock = $this->prophesize(TransformerInterface::class);
        $this->eventMock = $this->prophesize(TransformerEvent::class);
        $this->transformer = new TransformerChain([$this->transformerMock->reveal(), $this->transformerMock->reveal()]);
    }

    public function testCreate()
    {
        $this->transformerMock->supports($this->eventMock->reveal())->willReturn(true, false)->shouldBeCalledTimes(2);
        $this->transformerMock->create($this->eventMock->reveal())->shouldBeCalledTimes(1);
        $this->transformer->create($this->eventMock->reveal());
    }

    public function testUpdate()
    {
        $this->transformerMock->supports($this->eventMock->reveal())->willReturn(true, false)->shouldBeCalledTimes(2);
        $this->transformerMock->update($this->eventMock->reveal())->shouldBeCalledTimes(1);
        $this->transformer->update($this->eventMock->reveal());
    }

    public function testDelete()
    {
        $this->transformerMock->supports($this->eventMock->reveal())->willReturn(true, false)->shouldBeCalledTimes(2);
        $this->transformerMock->delete($this->eventMock->reveal())->shouldBeCalledTimes(1);
        $this->transformer->delete($this->eventMock->reveal());
    }
}
