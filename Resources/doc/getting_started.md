Getting started with CoopTilleulsMigrationBundle
------------------------------------------------

## Installation

Installing CoopTilleulsMigrationBundle can be done easily through [Composer](https://getcomposer.org/). Update your
`composer.json` file as following:

```
{
    ...
    "require": [
        ...
        "tilleuls/migration-bundle": "^1.0"
    ]
}
```

Register this bundle in your kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        new CoopTilleuls\MigrationBundle\CoopTilleulsMigrationBundle(),
        // ...
    ];

    // ...
}
```

## Configuration

### Configure legacy connection

**Assuming you have 2 bundles: AppBundle for your final application, and MigrationBundle which manages migration.**

This bundle requires to connect to 2 connections: default & legacy. Legacy connection represents the connection to the
old database:

```yml
doctrine:
    dbal:
        default_connection: default
        connections:
            default:
                driver:         "%database_driver%"
                host:           "%database_host%"
                port:           "%database_port%"
                dbname:         "%database_name%"
                user:           "%database_user%"
                password:       "%database_password%"
                charset:        "UTF8"
                server_version: "5.6"
            legacy:
                wrapper_class:  "CoopTilleuls\MigrationBundle\Doctrine\DBAL\DisabledConnection"
                driver:         "%legacy_database_driver%"
                host:           "%legacy_database_host%"
                port:           "%legacy_database_port%"
                dbname:         "%legacy_database_name%"
                user:           "%legacy_database_user%"
                password:       "%legacy_database_password%"
                charset:        "UTF8"
                server_version: "5.6"
    orm:
        default_entity_manager: default
        entity_managers:
            default:
                connection: default
                mappings:
                    AppBundle:  ~
            legacy:
                connection: legacy
                mappings:
                    MigrationBundle: ~
```

This configuration describes the 2 connections (`default` & `legacy`) with their own entity manager.

See the `wrapper_class` parameter on the legacy connection: it allows MigrationBundle to disable commit during the
transformers process due to security reasons. MigrationBundle has to ensure that if an error occurred in any transaction
(flush on the legacy database or on the current one) on the transformers process, all transactions must be rollbacked.

### Configure your application

Enable required configuration. You must provide the name of the Doctrine legacy connection:

```yml
# app/config/config.yml
coop_tilleuls_migration:
    legacy_connection_name: 'legacy'
```

## Loaders

At the init of the application, it'll be necessary to import data from the old database to the new one. This is the
main goal of _loaders_.

Read full documentation about [loaders](loaders.md).

## Transformers

Once both applications are online, it's necessary to keep both databases synchronized. To do so, the main system must
write data in the old database.

Read full documentation about [transformers](transformers.md).
