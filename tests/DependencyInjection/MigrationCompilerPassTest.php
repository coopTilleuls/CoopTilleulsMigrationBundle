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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MigrationCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $containerMock = $this->prophesize(ContainerBuilder::class);
        $definitionMock = $this->prophesize(Definition::class);

        $containerMock->findTaggedServiceIds('coop_tilleuls_migration.transformer')->willReturn([
            'foo'    => [['alias' => 'bar']],
            'lipsum' => [[]],
        ])->shouldBeCalledTimes(1);

        $containerMock->getDefinition('foo')->willReturn($definitionMock->reveal())->shouldBeCalledTimes(1);
        $definitionMock->getClass()->willReturn('\Foo\Bar', '\Lorem\Ipsum')->shouldBeCalledTimes(2);

        $containerMock->getDefinition('coop_tilleuls_migration.transformer.locator')->willReturn($definitionMock->reveal())->shouldBeCalledTimes(1);
        $definitionMock->replaceArgument(0, ['bar' => '\Foo\Bar', '\Lorem\Ipsum' => 'lipsum']);

        $compilerPass = new MigrationCompilerPass('coop_tilleuls_migration.transformer', 'coop_tilleuls_migration.transformer.locator');
        $compilerPass->process($containerMock->reveal());
    }
}
