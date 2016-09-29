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

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 * todo Make it final (conflict with unit tests)
 */
class TransformerEvent
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param object            $object
     * @param RegistryInterface $registry
     */
    public function __construct($object, RegistryInterface $registry)
    {
        $this->object = $object;
        $this->registry = $registry;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}
