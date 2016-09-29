<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\EventListener;

use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class TransformerEventListener
{
    private $transformer;
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

    public function __construct(RegistryInterface $registry, $connectionName, TransformerInterface $transformer)
    {
        $this->registry = $registry;
        $this->legacyConnection = $registry->getConnection($connectionName);
        $this->transformer = $transformer;
    }

    public function preFlush()
    {
        $this->legacyConnection->disable();
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $this->events['create'][] = $event;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->events['update'][] = $event;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $this->events['delete'][] = $event;
    }

    public function onFlush()
    {
        $this->process();
    }

    public function postFlush()
    {
        $this->process();
        $this->legacyConnection->enable();
        if ($this->legacyConnection->isTransactionActive()) {
            while (0 !== $this->legacyConnection->getTransactionNestingLevel()) {
                $this->legacyConnection->commit();
            }
        }
    }

    public function onError()
    {
        if ($this->legacyConnection->isTransactionActive()) {
            $this->legacyConnection->rollBack();
        }
    }

    private function process()
    {
        foreach ($this->events as $action => $events) {
            /* @var LifecycleEventArgs[] $events */
            foreach ($events as $event) {
                /** @var EntityManagerInterface $em */
                $em = $event->getObjectManager();
                /** @var UnitOfWork $uow */
                $uow = $em->getUnitOfWork();
                call_user_func([$this->transformer, $action], new TransformerEvent($event->getObject(), $this->registry));
                if ('delete' !== $action) {
                    $uow->recomputeSingleEntityChangeSet(
                        $em->getClassMetadata(ClassUtils::getClass($event->getObject())),
                        $event->getObject()
                    );
                }
            }
            $this->events[$action] = [];
        }
    }
}
