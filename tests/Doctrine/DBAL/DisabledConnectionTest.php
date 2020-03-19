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

namespace CoopTilleuls\MigrationBundle\tests\Doctrine\DBAL;

use CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class DisabledConnectionTest extends TestCase
{
    public function testDisable(): void
    {
        $driverMock = $this->prophesize(Driver::class);
        $driverMock->connect(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $connection = new DisabledConnection([], $driverMock->reveal());
        $connection->disable();
        $this->assertFalse($connection->isEnabled());
        $connection->commit();
    }

    public function testEnable(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('There is no active transaction.');

        $connection = new DisabledConnection([], $this->prophesize(Driver::class)->reveal());
        $connection->enable();
        $this->assertTrue($connection->isEnabled());
        $connection->commit();
    }
}
