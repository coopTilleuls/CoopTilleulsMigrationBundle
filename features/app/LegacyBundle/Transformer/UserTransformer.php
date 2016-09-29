<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Transformer;

use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Entity\User as LegacyUser;
use CoopTilleuls\MigrationBundle\Tests\TestBundle\Entity\User;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class UserTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(TransformerEvent $event)
    {
        $legacyUser = new LegacyUser();
        $legacyUser->setLogin($event->getObject()->getUsername());
        $legacyUser->setPswd($event->getObject()->getPassword());

        $event->getRegistry()->getManagerForClass(LegacyUser::class)->persist($legacyUser);
        $event->getRegistry()->getManagerForClass(LegacyUser::class)->flush();

        $event->getObject()->setLegacyId($legacyUser->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function update(TransformerEvent $event)
    {
        $legacyEm = $event->getRegistry()->getManagerForClass(LegacyUser::class);
        $legacyUser = $legacyEm->getRepository(LegacyUser::class)->find($event->getObject()->getLegacyId());
        if (!$legacyUser) {
            throw new \RuntimeException(sprintf('Unable to find legacy user %d', $event->getObject()->getLegacyId()));
        }

        $legacyUser->setLogin($event->getObject()->getUsername());
        $legacyUser->setPswd($event->getObject()->getPassword());

        $legacyEm->persist($legacyUser);
        $legacyEm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TransformerEvent $event)
    {
        $legacyEm = $event->getRegistry()->getManagerForClass(LegacyUser::class);
        $legacyUser = $legacyEm->getRepository(LegacyUser::class)->find($event->getObject()->getLegacyId());
        if (!$legacyUser) {
            throw new \RuntimeException(sprintf('Unable to find legacy user %d', $event->getObject()->getLegacyId()));
        }

        $legacyEm->remove($legacyUser);
        $legacyEm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TransformerEvent $event)
    {
        return $event->getObject() instanceof User;
    }
}
