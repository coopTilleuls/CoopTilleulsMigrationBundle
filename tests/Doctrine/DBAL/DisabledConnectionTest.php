<?php

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
use Doctrine\DBAL\Driver;
use Prophecy\Argument;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class DisabledConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDisable()
    {
        $driverMock = $this->prophesize(Driver::class);
        $driverMock->connect(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $connection = new DisabledConnection([], $driverMock->reveal());
        $connection->disable();
        $this->assertFalse($connection->isEnabled());
        $connection->commit();
    }

    /**
     * @expectedException \Doctrine\DBAL\ConnectionException
     * @expectedExceptionMessage There is no active transaction.
     */
    public function testEnable()
    {
        $connection = new DisabledConnection([], $this->prophesize(Driver::class)->reveal());
        $connection->enable();
        $this->assertTrue($connection->isEnabled());
        $connection->commit();
    }
}
