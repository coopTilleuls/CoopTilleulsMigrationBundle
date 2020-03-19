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

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class TransformerEvent
{
    private $object;
    private $registry;

    public function __construct(object $object, Registry $registry)
    {
        $this->object = $object;
        $this->registry = $registry;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
