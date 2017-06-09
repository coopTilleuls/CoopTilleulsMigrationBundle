<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Loader;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
abstract class AbstractLoader implements LoaderInterface
{
    private $logger;

    /**
     * @var Connection
     */
    private $legacyConnection;

    /**
     * @var int
     */
    protected $nbRows = 0;

    public function __construct(RegistryInterface $registry, $connectionName, LoggerInterface $logger = null)
    {
        $this->legacyConnection = $registry->getConnection($connectionName);
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRows()
    {
        return $this->nbRows;
    }

    /**
     * @return string
     */
    abstract protected function getQuery();

    /**
     * @param \stdClass $legacyRow
     */
    abstract protected function load(\stdClass $legacyRow);

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('loader');

        $this->logger->info('execute query in legacy database', [
            'loader' => self::class,
            'query' => $this->getQuery(),
        ]);
        $stmt = $this->legacyConnection->executeQuery($this->getQuery());
        $stmt->setFetchMode(\PDO::FETCH_OBJ);

        while ($legacyRow = $stmt->fetch()) {
            $this->load($legacyRow);
        }

        $event = $stopwatch->stop('loader');
        $this->logger->info('loader has been successfully executed', [
            'loader' => self::class,
            'nb_rows' => $this->getNbRows(),
            'duration' => sprintf('%.3F', $event->getDuration() / 1000).'s',
        ]);
    }

    /**
     * @param int      $newId
     * @param null|int $legacyId
     */
    protected function logUsage($newId, $legacyId = null)
    {
        $this->logger->info('loading in progress', [
            'loader' => self::class,
            'new_id' => $newId,
            'legacy_id' => $legacyId,
            'memory_peak' => round(memory_get_peak_usage() / 1000000).'M',
        ]);
    }
}
