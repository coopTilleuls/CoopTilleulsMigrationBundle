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

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class CoopTilleulsMigrationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('coop_tilleuls_migration.legacy_connection_name', $config['legacy_connection_name']);

        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(LoaderInterface::class)
                ->addTag('coop_tilleuls_migration.loader');
            $container->registerForAutoconfiguration(TransformerInterface::class)
                ->addTag('coop_tilleuls_migration.transformer');
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
