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

use Behat\Behat\Context\Context;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class FeatureContext implements Context
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @BeforeScenario
     */
    public function resetDatabase(): void
    {
        foreach ($this->doctrine->getManagers() as $manager) {
            $metadatas = $manager->getMetadataFactory()->getAllMetadata();

            $purger = new ORMPurger($manager);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

            try {
                $purger->purge();
            } catch (DBALException $e) {
                (new SchemaTool($manager))->createSchema($metadatas);
            }

            try {
                foreach ($metadatas as $metadata) {
                    $manager->getConnection()->executeUpdate(sprintf('DELETE FROM sqlite_sequence WHERE name=\'%s\';', $metadata->getTableName()));
                }
            } catch (DBALException $e) {
                continue;
            }
        }
    }
}
