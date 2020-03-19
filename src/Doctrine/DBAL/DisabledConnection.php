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

namespace CoopTilleuls\MigrationBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class DisabledConnection extends Connection
{
    private $enabled = true;

    /**
     * Disable commit.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Enable commit.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     *
     * @return void|bool
     */
    public function commit()
    {
        if (!$this->isEnabled()) {
            return;
        }

        return parent::commit();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
