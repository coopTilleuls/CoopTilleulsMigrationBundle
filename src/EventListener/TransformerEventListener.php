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

namespace CoopTilleuls\MigrationBundle\EventListener;

use CoopTilleuls\MigrationBundle\Annotation\Transformer;
use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class TransformerEventListener
{
    private $transformerLocator;
    private $reader;
    private $registry;
    private $events = [
        'create' => [],
        'update' => [],
        'delete' => [],
    ];

    /**
     * @var DisabledConnection
     */
    private $legacyConnection;

    public function __construct(Registry $registry, string $connectionName, ContainerInterface $transformerLocator, Reader $reader)
    {
        $this->registry = $registry;
        $this->legacyConnection = $registry->getConnection($connectionName);
        $this->transformerLocator = $transformerLocator;
        $this->reader = $reader;
    }

    public function preFlush(): void
    {
        $this->legacyConnection->disable();
    }

    public function prePersist($event): void
    {
        if ($this->hasTransformer($event->getObject())) {
            $this->events['create'][] = $event;
        }
    }

    public function preUpdate($event): void
    {
        if ($this->hasTransformer($event->getObject())) {
            $this->events['update'][] = $event;
        }
    }

    public function preRemove($event): void
    {
        if ($this->hasTransformer($event->getObject())) {
            $this->events['delete'][] = $event;
        }
    }

    public function onFlush(): void
    {
        $this->process();
    }

    public function postFlush(): void
    {
        $this->process();
        $this->legacyConnection->enable();
        if ($this->legacyConnection->isTransactionActive()) {
            while (0 !== $this->legacyConnection->getTransactionNestingLevel()) {
                $this->legacyConnection->commit();
            }
        }
    }

    public function onError(): void
    {
        if ($this->legacyConnection->isTransactionActive()) {
            $this->legacyConnection->rollBack();
        }
    }

    private function process(): void
    {
        foreach ($this->events as $action => $events) {
            foreach ($events as $event) {
                /** @var EntityManagerInterface $em */
                $em = $event->getObjectManager();
                /** @var UnitOfWork $uow */
                $uow = $em->getUnitOfWork();
                $object = $event->getObject();
                \call_user_func([$this->getTransformer($object), $action], new TransformerEvent($object, $this->registry));
                if ('delete' !== $action) {
                    $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(ClassUtils::getClass($object)), $object);
                }
            }
            $this->events[$action] = [];
        }
    }

    private function hasTransformer(object $object): bool
    {
        return null !== $this->getTransformerAnnotation($object) && $this->transformerLocator->has($this->getTransformerAnnotation($object)->transformer);
    }

    private function getTransformerAnnotation(object $object): ?Transformer
    {
        return $this->reader->getClassAnnotation(new \ReflectionClass(static::getRealClass($object)), Transformer::class);
    }

    private function getTransformer(object $object): TransformerInterface
    {
        return $this->transformerLocator->get($this->getTransformerAnnotation($object)->transformer);
    }

    private static function getRealClass($className): string
    {
        if (\is_object($className)) {
            $className = \get_class($className);
        }

        $positionCg = strrpos($className, '\\__CG__\\');
        $positionPm = strrpos($className, '\\__PM__\\');
        if ((false === $positionCg) && (false === $positionPm)) {
            return $className;
        }

        if (false !== $positionCg) {
            return substr($className, $positionCg + 8);
        }

        $className = ltrim($className, '\\');

        return substr(
            $className,
            8 + $positionPm,
            strrpos($className, '\\') - ($positionPm + 8)
        );
    }
}
