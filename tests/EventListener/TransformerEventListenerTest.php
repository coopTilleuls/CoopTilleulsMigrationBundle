<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\tests\EventListener;

use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEventListener;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class TransformerEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransformerEventListener
     */
    private $eventListener;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $registryMock;

    /**
     * @var ObjectProphecy|DisabledConnection
     */
    private $connectionMock;

    /**
     * @var ObjectProphecy|TransformerInterface
     */
    private $transformerMock;

    /**
     * @var ObjectProphecy|LifecycleEventArgs
     */
    private $eventMock;

    /**
     * @var \ReflectionProperty
     */
    private $reflectionProperty;

    protected function setUp()
    {
        $this->transformerMock = $this->prophesize(TransformerInterface::class);
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->connectionMock = $this->prophesize(DisabledConnection::class);
        $this->eventMock = $this->prophesize(LifecycleEventArgs::class);

        $this->registryMock->getConnection('legacy')->willReturn($this->connectionMock->reveal())->shouldBeCalledTimes(1);

        $this->eventListener = new TransformerEventListener(
            $this->registryMock->reveal(),
            'legacy',
            $this->transformerMock->reveal()
        );

        $this->reflectionProperty = new \ReflectionProperty(TransformerEventListener::class, 'events');
        $this->reflectionProperty->setAccessible(true);
    }

    public function testPreFlush()
    {
        $this->connectionMock->disable()->shouldBeCalledTimes(1);
        $this->eventListener->preFlush();
    }

    public function testPrePersist()
    {
        $this->eventListener->prePersist($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [$this->eventMock->reveal()],
            'update' => [],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreUpdate()
    {
        $this->eventListener->preUpdate($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [],
            'update' => [$this->eventMock->reveal()],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreRemove()
    {
        $this->eventListener->preRemove($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [],
            'update' => [],
            'delete' => [$this->eventMock->reveal()],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testOnError()
    {
        $this->connectionMock->isTransactionActive()->willReturn(true)->shouldBeCalledTimes(1);
        $this->connectionMock->rollBack()->shouldBeCalledTimes(1);
        $this->eventListener->onError();
    }

    public function testPostFlush()
    {
        $this->eventListener->prePersist($this->eventMock->reveal());
        $this->eventListener->preUpdate($this->eventMock->reveal());
        $this->eventListener->preRemove($this->eventMock->reveal());

        $emMock = $this->prophesize(EntityManagerInterface::class);
        $uowMock = $this->prophesize(UnitOfWork::class);
        $objectMock = $this->prophesize(\stdClass::class);
        $classMetadataMock = $this->prophesize(ClassMetadata::class);
        $this->eventMock->getObjectManager()->willReturn($emMock->reveal())->shouldBeCalledTimes(3);
        $emMock->getUnitOfWork()->willReturn($uowMock->reveal())->shouldBeCalledTimes(3);
        $this->eventMock->getObject()->willReturn($objectMock->reveal())->shouldBeCalledTimes(7);
        $this->transformerMock->create(Argument::that(function ($event) use ($objectMock) {
            return $event instanceof TransformerEvent &&
                   $this->registryMock->reveal() === $event->getRegistry() &&
                   $objectMock->reveal() === $event->getObject();
        }))->shouldBeCalledTimes(1);
        $this->transformerMock->update(Argument::that(function ($event) use ($objectMock) {
            return $event instanceof TransformerEvent &&
            $this->registryMock->reveal() === $event->getRegistry() &&
            $objectMock->reveal() === $event->getObject();
        }))->shouldBeCalledTimes(1);
        $this->transformerMock->delete(Argument::that(function ($event) use ($objectMock) {
            return $event instanceof TransformerEvent &&
            $this->registryMock->reveal() === $event->getRegistry() &&
            $objectMock->reveal() === $event->getObject();
        }))->shouldBeCalledTimes(1);
        $emMock->getClassMetadata(get_class($objectMock->reveal()))->willReturn($classMetadataMock->reveal())->shouldBeCalledTimes(2);
        $uowMock->recomputeSingleEntityChangeSet($classMetadataMock->reveal(), $objectMock->reveal())->shouldBeCalledTimes(2);

        $this->connectionMock->enable()->shouldBeCalledTimes(1);
        $this->connectionMock->isTransactionActive()->willReturn(true)->shouldBeCalledTimes(1);
        $this->connectionMock->getTransactionNestingLevel()->willReturn(2, 1, 0)->shouldBeCalledTimes(3);
        $this->connectionMock->commit()->shouldBeCalledTimes(2);

        $this->eventListener->postFlush();
        $this->assertEquals(['create' => [], 'update' => [], 'delete' => []], $this->reflectionProperty->getValue($this->eventListener));
    }
}
