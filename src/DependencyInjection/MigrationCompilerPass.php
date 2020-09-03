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

namespace CoopTilleuls\MigrationBundle\DependencyInjection;

use CoopTilleuls\MigrationBundle\Loader\AbstractLoader;
use Doctrine\Common\Inflector\Inflector;
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
    public function process(ContainerBuilder $container): void
    {
        $services = [];
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $reflection = new \ReflectionClass($class);

            // Override AbstractLoader parameters for autowiring
            if ($reflection->isSubclassOf(AbstractLoader::class)) {
                $definition->replaceArgument('$connectionName', $container->getParameter('coop_tilleuls_migration.legacy_connection_name'));
            }

            // Generate aliases
            $alias = Inflector::tableize(preg_replace('/^(.*)Loader$/i', '$1', $reflection->getShortName()));
            $aliases = [$class, $alias, str_replace('_', '-', $alias)];
            if (true === $this->allowAlias && isset($attributes[0]['alias'])) {
                $aliases[] = $attributes[0]['alias'];
            }
            foreach (array_unique($aliases) as $alias) {
                $services[$alias] = new Reference($id);
            }
        }

        $container->getDefinition($this->locator)->replaceArgument(0, $services);
    }
}
