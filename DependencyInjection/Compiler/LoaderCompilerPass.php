<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class LoaderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $loaders = [];
        foreach ($container->findTaggedServiceIds('coop_tilleuls_migration.loader') as $id => $attributes) {
            if (!isset($attributes[0]['alias']) || empty($attributes[0]['alias'])) {
                throw new \LogicException(sprintf(
                    'You must provide an `alias` attribute to the tag "coop_tilleuls_migration.loader" for service "%s".',
                    $id
                ));
            }
            $priority = isset($attributes[0]['priority']) ? (int) $attributes[0]['priority'] : 0;
            $loaders[$priority][$attributes[0]['alias']] = new Reference($id);
        }
        krsort($loaders);

        $container->getDefinition('coop_tilleuls_migration.loader.registry')->replaceArgument(
            0,
            $loaders ? call_user_func_array('array_merge', $loaders) : $loaders
        );
    }
}
