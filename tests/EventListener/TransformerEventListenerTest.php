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

namespace CoopTilleuls\MigrationBundle\Tests\EventListener;

use CoopTilleuls\MigrationBundle\Annotation\Transformer;
use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEventListener;
use CoopTilleuls\MigrationBundle\Tests\ProphecyTrait;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class TransformerEventListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var TransformerEventListener
     */
    private $eventListener;

    /**
     * @var ObjectProphecy|Registry
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
     * @var ObjectProphecy|\Doctrine\Common\Persistence\Event\LifecycleEventArgs|\Doctrine\Persistence\Event\LifecycleEventArgs
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

    protected function setUp(): void
    {
        $this->transformerMock = $this->prophesize(TransformerInterface::class);
        $this->registryMock = $this->prophesize(Registry::class);
        $this->locatorMock = $this->prophesize(ContainerInterface::class);
        $this->readerMock = $this->prophesize(Reader::class);
        $this->connectionMock = $this->prophesize(DisabledConnection::class);
        $this->annotationMock = $this->prophesize(Transformer::class);
        if (class_exists(\Doctrine\Common\Persistence\Event\LifecycleEventArgs::class)) {
            $this->eventMock = $this->prophesize(\Doctrine\Common\Persistence\Event\LifecycleEventArgs::class);
        } else {
            $this->eventMock = $this->prophesize(\Doctrine\Persistence\Event\LifecycleEventArgs::class);
        }
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

    public function testPreFlush(): void
    {
        $this->connectionMock->disable()->shouldBeCalledTimes(1);
        $this->eventListener->preFlush();
    }

    public function testPrePersist(): void
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(2);
        $this->locatorMock->has(Argument::any())->willReturn(true)->shouldBeCalledTimes(1);

        $this->eventListener->prePersist($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [$this->eventMock->reveal()],
            'update' => [],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreUpdate(): void
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(2);
        $this->locatorMock->has(Argument::any())->willReturn(true)->shouldBeCalledTimes(1);

        $this->eventListener->preUpdate($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [],
            'update' => [$this->eventMock->reveal()],
            'delete' => [],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testPreRemove(): void
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(1);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(2);
        $this->locatorMock->has(Argument::any())->willReturn(true)->shouldBeCalledTimes(1);

        $this->eventListener->preRemove($this->eventMock->reveal());
        $this->assertEquals([
            'create' => [],
            'update' => [],
            'delete' => [$this->eventMock->reveal()],
        ], $this->reflectionProperty->getValue($this->eventListener));
    }

    public function testOnError(): void
    {
        $this->connectionMock->isTransactionActive()->willReturn(true)->shouldBeCalledTimes(1);
        $this->connectionMock->rollBack()->shouldBeCalledTimes(1);
        $this->eventListener->onError();
    }

    public function testPostFlush(): void
    {
        $this->eventMock->getObject()->willReturn($this->objectMock)->shouldBeCalledTimes(6);
        $this->readerMock->getClassAnnotation(Argument::type('\ReflectionClass'), Transformer::class)
            ->willReturn($this->annotationMock)
            ->shouldBeCalledTimes(9);
        $this->locatorMock->has(Argument::any())->willReturn(true)->shouldBeCalledTimes(3);
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
        $emMock->getClassMetadata(\get_class($objectMock->reveal()))->willReturn($classMetadataMock)->shouldBeCalledTimes(2);
        $uowMock->recomputeSingleEntityChangeSet($classMetadataMock->reveal(), $objectMock->reveal())->shouldBeCalledTimes(2);

        $this->connectionMock->enable()->shouldBeCalledTimes(1);
        $this->connectionMock->isTransactionActive()->willReturn(true)->shouldBeCalledTimes(1);
        $this->connectionMock->getTransactionNestingLevel()->willReturn(2, 1, 0)->shouldBeCalledTimes(3);
        $this->connectionMock->commit()->shouldBeCalledTimes(2);

        $this->eventListener->postFlush();
        $this->assertEquals(['create' => [], 'update' => [], 'delete' => []], $this->reflectionProperty->getValue($this->eventListener));
    }
}
