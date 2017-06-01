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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class LoaderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $containerMock = $this->prophesize(ContainerBuilder::class);
        $definitionMock = $this->prophesize(Definition::class);

        $compilerPass = new LoaderCompilerPass();

        $containerMock->findTaggedServiceIds('coop_tilleuls_migration.loader')->willReturn([
            'foo' => [[]],
            'lorem' => [[]],
            'dolor' => [[]],
        ])->shouldBeCalledTimes(1);

        $containerMock->getDefinition('coop_tilleuls_migration.loader.registry')->willReturn($definitionMock->reveal())->shouldBeCalledTimes(1);
        $definitionMock->replaceArgument(0, [
            new Reference('foo'),
            new Reference('lorem'),
            new Reference('dolor'),
        ])->shouldBeCalledTimes(1);

        $compilerPass->process($containerMock->reveal());
    }
}
