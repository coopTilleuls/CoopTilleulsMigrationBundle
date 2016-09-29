<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Tests\Compiler;

use CoopTilleuls\MigrationBundle\DependencyInjection\Compiler\LoaderCompilerPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class LoaderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    private $compilerPass;
    private $containerMock;
    private $definitionMock;

    protected function setUp()
    {
        $this->containerMock = $this->prophesize(ContainerBuilder::class);
        $this->definitionMock = $this->prophesize(Definition::class);

        $this->compilerPass = new LoaderCompilerPass();
    }

    public function testProcess()
    {
        $this->containerMock->findTaggedServiceIds('coop_tilleuls_migration.loader')->willReturn([
            'foo' => [
                [
                    'alias' => 'bar',
                ],
            ],
            'lorem' => [
                [
                    'alias' => 'ipsum',
                    'priority' => 2,
                ],
            ],
            'dolor' => [
                [
                    'alias' => 'sit',
                    'priority' => 1,
                ],
            ],
        ])->shouldBeCalledTimes(1);

        $this->containerMock->getDefinition('coop_tilleuls_migration.loader.registry')->willReturn($this->definitionMock->reveal())->shouldBeCalledTimes(1);
        $this->definitionMock->replaceArgument(0, [
            'ipsum' => new Reference('lorem'),
            'sit' => new Reference('dolor'),
            'bar' => new Reference('foo'),
        ])->shouldBeCalledTimes(1);

        $this->compilerPass->process($this->containerMock->reveal());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must provide an `alias` attribute to the tag "coop_tilleuls_migration.loader" for service "foo".
     */
    public function testProcessException()
    {
        $this->containerMock->findTaggedServiceIds('coop_tilleuls_migration.loader')->willReturn(['foo' => [[]]])->shouldBeCalledTimes(1);
        $this->definitionMock->replaceArgument(0, Argument::any())->shouldNotBeCalled();

        $this->compilerPass->process($this->containerMock->reveal());
    }
}
