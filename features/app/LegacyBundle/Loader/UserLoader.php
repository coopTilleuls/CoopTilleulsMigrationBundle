<?php

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
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class UserLoader extends AbstractLoader
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(RegistryInterface $registry, $connectionName)
    {
        parent::__construct($registry, $connectionName);
        $this->connection = $registry->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function getQuery()
    {
        return 'SELECT * FROM user WHERE is_deleted = 0';
    }

    /**
     * {@inheritdoc}
     */
    protected function load(\stdClass $legacyRow)
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
