<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\tests\Command;

use CoopTilleuls\MigrationBundle\Command\MigrationLoadCommand;
use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MigrationLoadCommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;
    private $locatorMock;
    private $inputMock;
    private $loaderMock;
    private $outputMock;
    private $styleMock;
    private $reflection;

    protected function setUp()
    {
        $this->locatorMock = $this->prophesize(ContainerInterface::class);
        $this->styleMock = $this->prophesize(StyleInterface::class);
        $this->inputMock = $this->prophesize(InputInterface::class);
        $this->outputMock = $this->prophesize(OutputInterface::class);
        $this->loaderMock = $this->prophesize(LoaderInterface::class);

        $this->reflection = new \ReflectionMethod(MigrationLoadCommand::class, 'execute');
        $this->reflection->setAccessible(true);
        $this->command = new MigrationLoadCommand($this->locatorMock->reveal());
        $this->command->setStyle($this->styleMock->reveal());

        $this->inputMock->getArgument('loader')->willReturn('user')->shouldBeCalledTimes(1);
    }

    public function testExecute()
    {
        $this->locatorMock->has('user')->willReturn(true)->shouldBeCalledTimes(1);
        $this->locatorMock->get('user')->willReturn($this->loaderMock)->shouldBeCalledTimes(1);
        $this->styleMock->title('Loading data from loader "user"')->shouldBeCalledTimes(1);
        $this->loaderMock->execute()->shouldBeCalledTimes(1);

        $this->loaderMock->getNbRows()->willReturn(2)->shouldBeCalledTimes(2);
        $this->styleMock->text('2 record(s) successfully loaded')->shouldBeCalledTimes(1);
        $this->styleMock->success('Loader "user" successfully executed')->shouldBeCalledTimes(1);

        $this->reflection->invoke($this->command, $this->inputMock->reveal(), $this->outputMock->reveal());
    }

    /**
     * @expectedException \CoopTilleuls\MigrationBundle\Exception\LoaderNotFoundException
     * @expectedExceptionMessage Cannot find loader "user".
     */
    public function testExecuteNoLoader()
    {
        $this->locatorMock->has('user')->willReturn(false)->shouldBeCalledTimes(1);
        $this->locatorMock->get(Argument::any())->shouldNotBeCalled();

        $this->reflection->invoke($this->command, $this->inputMock->reveal(), $this->outputMock->reveal());
    }

    public function testExecuteNoData()
    {
        $this->locatorMock->has('user')->willReturn(true)->shouldBeCalledTimes(1);
        $this->locatorMock->get('user')->willReturn($this->loaderMock)->shouldBeCalledTimes(1);
        $this->styleMock->title('Loading data from loader "user"')->shouldBeCalledTimes(1);
        $this->loaderMock->execute()->shouldBeCalledTimes(1);

        $this->loaderMock->getNbRows()->willReturn(0)->shouldBeCalledTimes(1);
        $this->styleMock->text('No data loaded')->shouldBeCalledTimes(1);
        $this->styleMock->success('Loader "user" successfully executed')->shouldBeCalledTimes(1);

        $this->reflection->invoke($this->command, $this->inputMock->reveal(), $this->outputMock->reveal());
    }
}
