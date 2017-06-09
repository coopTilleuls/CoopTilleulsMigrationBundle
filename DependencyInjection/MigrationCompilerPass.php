<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class MigrationCompilerPass implements CompilerPassInterface
{
    private $tag;
    private $locator;
    private $allowAlias;

    public function __construct($tag, $locator, $allowAlias = false)
    {
        $this->tag = $tag;
        $this->locator = $locator;
        $this->allowAlias = $allowAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $services = [];
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $attributes) {
            $alias = $container->getDefinition($id)->getClass();
            if (true === $this->allowAlias && isset($attributes[0]['alias'])) {
                $alias = $attributes[0]['alias'];
            }
            $services[$alias] = new Reference($id);
        }

        $container->getDefinition($this->locator)->replaceArgument(0, $services);
    }
}
