<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 * todo Make it final (conflict with unit tests)
 */
class DisabledConnection extends Connection
{
    private $enabled = true;

    /**
     * Disable commit.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable commit.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if (!$this->isEnabled()) {
            return;
        }

        parent::commit();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
