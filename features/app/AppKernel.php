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

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Test purpose micro-kernel.
 *
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function getCacheDir()
    {
        return __DIR__.'/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return __DIR__.'/logs/'.$this->getEnvironment();
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle(),
            new CoopTilleuls\MigrationBundle\CoopTilleulsMigrationBundle(),
            new CoopTilleuls\MigrationBundle\Tests\TestBundle\TestBundle(),
            new CoopTilleuls\MigrationBundle\Tests\LegacyBundle\LegacyBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('coop_tilleuls_migration', [
            'legacy_connection_name' => 'legacy',
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'driver' => 'pdo_sqlite',
                        'path' => '%kernel.cache_dir%/default.sqlite',
                        'charset' => 'UTF8',
                    ],
                    'legacy' => [
                        'wrapper_class' => 'CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection',
                        'driver' => 'pdo_sqlite',
                        'path' => '%kernel.cache_dir%/legacy.sqlite',
                        'charset' => 'UTF8',
                    ],
                ],
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'default_entity_manager' => 'default',
                'entity_managers' => [
                    'default' => [
                        'connection' => 'default',
                        'mappings' => [
                            'TestBundle' => null,
                        ],
                        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                    ],
                    'legacy' => [
                        'connection' => 'legacy',
                        'mappings' => [
                            'LegacyBundle' => null,
                        ],
                        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                    ],
                ],
            ],
        ]);

        $c->loadFromExtension('framework', [
            'secret' => 'CoopTilleulsMigrationBundle',
            'test' => null,
            'profiler' => ['collect' => false],
        ]);
    }
}
