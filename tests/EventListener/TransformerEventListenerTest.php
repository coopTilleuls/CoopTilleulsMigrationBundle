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

use CoopTilleuls\MigrationBundle\Annotation\Transformer;
use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEventListener;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
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
     * @var ObjectProphecy|ContainerInterface
     */
    private $locatorMock;

    /**
     * @var ObjectProphecy|Reader
     */
    private $readerMock;

    /**
     * @var ObjectProphecy|DisabledConnection
     */
    private $connectionMock;

    /**
     * @var ObjectProphecy|TransformerInterface
     */
    private $transformerMock;

    /**
     * @var ObjectProphecy|Transformer
     */
    private $annotationMock;

    /**
     * @var ObjectProphecy|LifecycleEventArgs
     */
    private $eventMock;

    /**
     * @var ObjectProphecy
     */
    private $objectMock;

    /**
     * @var \ReflectionProperty
     */
    private $reflectionProperty;

    protected function setUp()
    {
        $this->transformerMock = $this->prophesize(TransformerInterface::class);
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->locatorMock = $this->prophesize(ContainerInterface::class);
        $this->readerMock = $this->prophesize(Reader::class);
        $this->connectionMock = $this->prophesize(DisabledConnection::class);
        $this->annotationMock = $this->prophesize(Transformer::class);
        $this->eventMock = $this->prophesize(LifecycleEventArgs::class);
        $this->objectMock = $this->prophesize(\stdClass::class);

        $this->registryMock->getConnection('legacy')->willReturn($this->connectionMock)->shouldBeCalledTimes(1);

        $this->eventListener = new TransformerEventListener(
            $this->registryMock->reveal(),
            'legacy',
            $this->locatorMock->reveal(),
            $this->readerMock->reveal()
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
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(1);

        $this->eventListener->prePersist($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [$this->eventMock->reveal()],
            'update' => [],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreUpdate()
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(1);

        $this->eventListener->preUpdate($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [],
            'update' => [$this->eventMock->reveal()],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreRemove()
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(1);

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
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(6);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(6);
        $this->locatorMock->get(Argument::any())->willReturn($this->transformerMock)->shouldBeCalledTimes(3);

        $this->eventListener->prePersist($this->eventMock->reveal());
        $this->eventListener->preUpdate($this->eventMock->reveal());
        $this->eventListener->preRemove($this->eventMock->reveal());

        $emMock = $this->prophesize(EntityManagerInterface::class);
        $uowMock = $this->prophesize(UnitOfWork::class);
        $objectMock = $this->objectMock;
        $classMetadataMock = $this->prophesize(ClassMetadata::class);

        $this->eventMock->getObjectManager()->willReturn($emMock)->shouldBeCalledTimes(3);
        $emMock->getUnitOfWork()->willReturn($uowMock)->shouldBeCalledTimes(3);
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
        $emMock->getClassMetadata(get_class($objectMock->reveal()))->willReturn($classMetadataMock)->shouldBeCalledTimes(2);
        $uowMock->recomputeSingleEntityChangeSet($classMetadataMock->reveal(), $objectMock->reveal())->shouldBeCalledTimes(2);

        $this->connectionMock->enable()->shouldBeCalledTimes(1);
        $this->connectionMock->isTransactionActive()->willReturn(true)->shouldBeCalledTimes(1);
        $this->connectionMock->getTransactionNestingLevel()->willReturn(2, 1, 0)->shouldBeCalledTimes(3);
        $this->connectionMock->commit()->shouldBeCalledTimes(2);

        $this->eventListener->postFlush();
        $this->assertEquals(['create' => [], 'update' => [], 'delete' => []], $this->reflectionProperty->getValue($this->eventListener));
    }
}
