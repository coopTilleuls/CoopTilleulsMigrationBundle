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

use Behat\Behat\Context\Context;
use CoopTilleuls\MigrationBundle\E2e\LegacyBundle\Entity\User as LegacyUser;
use CoopTilleuls\MigrationBundle\E2e\TestBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Assert;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class TransformerContext implements Context
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @When I create a user
     */
    public function iCreateAUser(): void
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(sha1('password'));

        $em = $this->doctrine->getManagerForClass(User::class);
        $em->persist($user);
        $em->flush();

        $em->clear();
        $this->doctrine->getManagerForClass(LegacyUser::class)->clear();
    }

    /**
     * @Then user must be created in legacy database
     */
    public function userMustBeCreatedInLegacyDatabase(): void
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        Assert::assertNotNull($user, 'Cannot retrieve user with id 1');
        Assert::assertNotNull($user->getLegacyId(), 'User does not have legacyId property filled');

        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find($user->getLegacyId());
        Assert::assertNotNull($legacyUser, 'Cannot retrieve legacy user with id '.$user->getLegacyId());
        Assert::assertEquals('admin', $legacyUser->getLogin());
        Assert::assertEquals(sha1('password'), $legacyUser->getPswd());
    }

    /**
     * @When I update a user
     */
    public function iUpdateAUser(): void
    {
        $this->iCreateAUser();

        $em = $this->doctrine->getManagerForClass(User::class);
        $user = $em->getRepository(User::class)->find(1);
        $user->setUsername('foo');
        $user->setPassword(sha1('bar'));

        $em->persist($user);
        $em->flush();

        $em->clear();
        $this->doctrine->getManagerForClass(LegacyUser::class)->clear();
    }

    /**
     * @Then user must be updated in legacy database
     */
    public function userMustBeUpdatedInLegacyDatabase(): void
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        Assert::assertNotNull($user, 'Cannot retrieve user with id 1');
        Assert::assertNotNull($user->getLegacyId(), 'User does not have legacyId property filled');
        Assert::assertEquals('foo', $user->getUsername());
        Assert::assertEquals(sha1('bar'), $user->getPassword());

        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find($user->getLegacyId());
        Assert::assertNotNull($legacyUser, 'Cannot retrieve legacy user with id '.$user->getLegacyId());
        Assert::assertEquals('foo', $legacyUser->getLogin());
        Assert::assertEquals(sha1('bar'), $legacyUser->getPswd());
    }

    /**
     * @When I delete a user
     */
    public function iDeleteAUser(): void
    {
        $this->iCreateAUser();

        $em = $this->doctrine->getManagerForClass(User::class);
        $user = $em->getRepository(User::class)->find(1);
        $em->remove($user);
        $em->flush();

        $em->clear();
        $this->doctrine->getManagerForClass(LegacyUser::class)->clear();
    }

    /**
     * @Then user must be deleted from legacy database
     */
    public function userMustBeDeletedFromLegacyDatabase(): void
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        Assert::assertNull($user, 'User has not been removed from database');
        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find(1);
        Assert::assertNull($legacyUser, 'Legacy user has not been removed from database');
    }
}
