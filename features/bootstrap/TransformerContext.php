<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Entity\User as LegacyUser;
use CoopTilleuls\MigrationBundle\Tests\TestBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;

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
    public function iCreateAUser()
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
    public function userMustBeCreatedInLegacyDatabase()
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        \PHPUnit_Framework_Assert::assertNotNull($user, 'Cannot retrieve user with id 1');
        \PHPUnit_Framework_Assert::assertNotNull($user->getLegacyId(), 'User does not have legacyId property filled');

        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find($user->getLegacyId());
        \PHPUnit_Framework_Assert::assertNotNull($legacyUser, 'Cannot retrieve legacy user with id '.$user->getLegacyId());
        \PHPUnit_Framework_Assert::assertEquals('admin', $legacyUser->getLogin());
        \PHPUnit_Framework_Assert::assertEquals(sha1('password'), $legacyUser->getPswd());
    }

    /**
     * @When I update a user
     */
    public function iUpdateAUser()
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
    public function userMustBeUpdatedInLegacyDatabase()
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        \PHPUnit_Framework_Assert::assertNotNull($user, 'Cannot retrieve user with id 1');
        \PHPUnit_Framework_Assert::assertNotNull($user->getLegacyId(), 'User does not have legacyId property filled');
        \PHPUnit_Framework_Assert::assertEquals('foo', $user->getUsername());
        \PHPUnit_Framework_Assert::assertEquals(sha1('bar'), $user->getPassword());

        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find($user->getLegacyId());
        \PHPUnit_Framework_Assert::assertNotNull($legacyUser, 'Cannot retrieve legacy user with id '.$user->getLegacyId());
        \PHPUnit_Framework_Assert::assertEquals('foo', $legacyUser->getLogin());
        \PHPUnit_Framework_Assert::assertEquals(sha1('bar'), $legacyUser->getPswd());
    }

    /**
     * @When I delete a user
     */
    public function iDeleteAUser()
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
    public function userMustBeDeletedFromLegacyDatabase()
    {
        $user = $this->doctrine->getManagerForClass(User::class)->getRepository(User::class)->find(1);
        \PHPUnit_Framework_Assert::assertNull($user, 'User has not been removed from database');
        $legacyUser = $this->doctrine->getManagerForClass(LegacyUser::class)->getRepository(LegacyUser::class)->find(1);
        \PHPUnit_Framework_Assert::assertNull($legacyUser, 'Legacy user has not been removed from database');
    }
}
