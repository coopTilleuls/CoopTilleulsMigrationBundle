Transformers
------------

Transformers are Symfony services that will write data in legacy database once it is written in new database. It ensures
both databases are synchronized with the same data. To do so, it connects to Doctrine events.

As migration is a temporary process, it is recommended to implement transformers in a `MigrationBundle` which can be
easily removed once migration finished.

## Create a transformer

A transformer is just a Symfony service with a tag. For example:

```yml
# services.yml
services:
    legacy.transformer.user:
        class: MigrationBundle\Transformer\UserTransformer
        tags:
            - { name: coop_tilleuls_migration.transformer }
```

> Note: using Symfony 3.3 autowiring, this configuration is already registered. You don't have anything to declare :D

But you need to add an annotation in your entity to connect it with the right transformer:

```php
namespace AppBundle\Entity;

use CoopTilleuls\MigrationBundle\Annotation\Transformer;

/**
 * @Transformer("MigrationBundle\Transformer\UserTransformer")
 */
class User
{
}
```

The class is really simple as it just implements `TransformerInterface`:

```php
namespace MigrationBundle\Transformer;

use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;

final class UserTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(TransformerEvent $event)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function update(TransformerEvent $event)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TransformerEvent $event)
    {
    }
}
```

## Legacy mapping

The main purpose of transformers is that they require a Doctrine mapping of the legacy database: you need to create a
mapping of entities representing your legacy database (for example: `MigrationBundle\Entity\User`).

Once this mapping ready, it is really easy to create legacy records in your transformer, as following:

```php
namespace MigrationBundle\Transformer;

use AppBundle\Entity\User;
use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;
use CoopTilleuls\MigrationBundle\Transformer\TransformerInterface;
use MigrationBundle\Entity\User as LegacyUser;

final class UserTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(TransformerEvent $event)
    {
        $legacyUser = new LegacyUser();
        $legacyUser->setLogin($event->getObject()->getUsername());
        $legacyUser->setPswd($event->getObject()->getPassword());

        $event->getRegistry()->getManagerForClass(LegacyUser::class)->persist($legacyUser);
        $event->getRegistry()->getManagerForClass(LegacyUser::class)->flush();

        $event->getObject()->setLegacyId($legacyUser->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function update(TransformerEvent $event)
    {
        $legacyEm = $event->getRegistry()->getManagerForClass(LegacyUser::class);
        $legacyUser = $legacyEm->getRepository(LegacyUser::class)->find($event->getObject()->getLegacyId());
        if (!$legacyUser) {
            // Legacy user cannot be found (error? normal? it's up to you, you can also throw a \RuntimeException)
            return;
        }

        $legacyUser->setLogin($event->getObject()->getUsername());
        $legacyUser->setPswd($event->getObject()->getPassword());

        $legacyEm->persist($legacyUser);
        $legacyEm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TransformerEvent $event)
    {
        $legacyEm = $event->getRegistry()->getManagerForClass(LegacyUser::class);
        $legacyUser = $legacyEm->getRepository(LegacyUser::class)->find($event->getObject()->getLegacyId());
        if (!$legacyUser) {
            // Legacy user cannot be found (error? normal? it's up to you, you can also throw a \RuntimeException)
            return;
        }

        $legacyEm->remove($legacyUser);
        $legacyEm->flush();
    }
}
```

Here is the declaration of the previous service:

```yml
# services.yml
services:
    legacy.transformer.user:
        class: MigrationBundle\Transformer\UserTransformer
        tags:
            - { name: coop_tilleuls_migration.transformer }
```
