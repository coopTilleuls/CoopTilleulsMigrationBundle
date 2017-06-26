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

use CoopTilleuls\MigrationBundle\DependencyInjection\MigrationCompilerPass;
use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MigrationCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $containerMock = $this->prophesize(ContainerBuilder::class);
        $definitionMock = $this->prophesize(Definition::class);
        $locatorDefinitionMock = $this->prophesize(Definition::class);

        $containerMock->findTaggedServiceIds('coop_tilleuls_migration.transformer')->willReturn([
            'CoopTilleuls\MigrationBundle\Tests\Compiler\FooLoader' => [[]],
            'CoopTilleuls\MigrationBundle\Tests\Compiler\BarLoader' => [['alias' => 'bar']],
        ])->shouldBeCalledTimes(1);

        $containerMock->getDefinition('CoopTilleuls\MigrationBundle\Tests\Compiler\FooLoader')->willReturn($definitionMock->reveal())->shouldBeCalledTimes(1);
        $containerMock->getDefinition('CoopTilleuls\MigrationBundle\Tests\Compiler\BarLoader')->willReturn($definitionMock->reveal())->shouldBeCalledTimes(1);
        $definitionMock->getClass()->willReturn('CoopTilleuls\MigrationBundle\Tests\Compiler\FooLoader', 'CoopTilleuls\MigrationBundle\Tests\Compiler\BarLoader')->shouldBeCalledTimes(2);

        $containerMock->getDefinition('coop_tilleuls_migration.transformer.locator')->willReturn($locatorDefinitionMock->reveal())->shouldBeCalledTimes(1);
        $locatorDefinitionMock->replaceArgument(0, Argument::type('array'))->shouldBeCalledTimes(1);

        $compilerPass = new MigrationCompilerPass('coop_tilleuls_migration.transformer', 'coop_tilleuls_migration.transformer.locator', true);
        $compilerPass->process($containerMock->reveal());
    }
}

class FooLoader
{}

class BarLoader
{}
