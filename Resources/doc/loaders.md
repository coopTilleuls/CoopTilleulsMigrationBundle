Loaders
-------

Loaders are scripts that import data from the legacy database to the new one. For performances reasons, they're
developed in database language (such as MySQL through PDO).

As migration is a temporary process, it is recommended to implement loaders in a `MigrationBundle` which can be easily
removed once migration finished.

## Create a loader

A loader is just a Symfony service with a tag. For example, let's load users:

```yml
# services.yml
services:
    legacy.loader.user:
        class: MigrationBundle\Loader\UserLoader
        tags:
            - { name: coop_tilleuls_migration.loader, alias: user }
```

The class is really simple as it just implements `LoaderInterface`:

```php
namespace MigrationBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;

final class UserLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRows()
    {
    }
}
```

Method `getName` must return the name of the loader (which can be similar to its alias in service declaration). Method
`getNbRows` must return the total of loaded rows (for log usage). Method `execute` will contain the migration script.

As you can imagine, `getNbRows` & `execute` methods may be similar in some loaders, except about the query to execute.
That's why an `AbstractLoader` can be used. It already implements `getNbRows` & `execute` methods, ready to use. You
just need to implement code specific to your loader: methods `getName`, `load` & `getQuery`.

## Configure loader

Let's use `AbstractLoader` and only develop the specific methods to current loader:

```php
namespace MigrationBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\AbstractLoader;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

final class UserLoader extends AbstractLoader
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(RegistryInterface $registry, $connectionName, LoggerInterface $logger = null)
    {
        parent::__construct($registry, $connectionName, $logger);
        $this->connection = $registry->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    protected function getQuery()
    {
        // Return query which select users in legacy database
        return 'SELECT * FROM user WHERE is_deleted = 0';
    }

    /**
     * {@inheritdoc}
     */
    protected function load(\stdClass $legacyRow)
    {
        // Retrieve user id (in legacy database) from parsed row
        $legacyId = $legacyRow->id;

        // Insert new user in current database
        $this->connection->insert('user', [
            'username' => $legacyRow->login,
            'legacy_id' => $legacyId,
        ]);
        ++$this->nbRows;
        $this->logUsage($this->connection->lastInsertId(), $legacyId);
    }
}
```

`load` method will manage to insert data row by row in current database from legacy database query results.

Service declaration must be updated:

```yml
# services.yml
services:
    legacy.loader.user:
        class: MigrationBundle\Loader\UserLoader
        parent: coop_tilleuls_migration.loader.abstract
        tags:
            - { name: coop_tilleuls_migration.loader, alias: user }
```

## Execute loader

A Symfony command is ready in this bundle, which can be used to execute a specific loader:

```bash
php bin/console migration:load user
```

**Change `user` with the name of the loader you want to execute.**

## Complex loaders

It can happen (and it will, really!) that sometimes a loader cannot be as simple as this. You may need a more complex
query, or directly load data from database to database without a `load` method in the middle of the process.

To do so, it is recommended to develop your own loader implementing `LoaderInterface`. For example:

```php
namespace MigrationBundle\Loader;

use CoopTilleuls\MigrationBundle\Loader\LoaderInterface;
use Doctrine\DBAL\Connection;

final class UserLoader implements LoaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Connection
     */
    private $legacyConnection;

    /**
     * @var int
     */
    private $nbRows;

    public function __construct(RegistryInterface $registry)
    {
        $this->connection = $registry->getConnection();
        $this->legacyConnection = $registry->getConnection('legacy');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRows()
    {
        return $this->nbRows;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->nbRows = $this->connection->executeUpdate(sprintf(<<<SQL
INSERT INTO user (username, legacy_id, state)
SELECT
    legacy.login,
    legacy.id,
    CASE
        WHEN legacy.state = "FOO" THEN "bar"
        WHEN legacy.state = "LOREM" THEN "ipsum"
        ELSE "anything"
    END
FROM %s.user AS legacy
WHERE legacy.is_deleted = 0
SQL
            , $this->legacyConnection->getDatabase()
        ));
    }
}
```
