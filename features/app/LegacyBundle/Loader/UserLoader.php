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

namespace CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\AbstractLoader;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class UserLoader extends AbstractLoader
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Registry $registry, string $connectionName)
    {
        parent::__construct($registry, $connectionName);
        $this->connection = $registry->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function getQuery(): string
    {
        return 'SELECT * FROM user WHERE is_deleted = 0';
    }

    /**
     * {@inheritdoc}
     */
    protected function load(\stdClass $legacyRow): void
    {
        $legacyId = $legacyRow->id;

        $this->connection->insert('user', [
            'username' => $legacyRow->login,
            'password' => $legacyRow->pswd,
            'legacy_id' => $legacyId,
        ]);
        ++$this->nbRows;

        $this->logUsage($this->connection->lastInsertId(), $legacyId);
    }
}
