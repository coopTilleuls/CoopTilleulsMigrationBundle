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
use CoopTilleuls\MigrationBundle\Command\MigrationLoadCommand;
use CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Entity\User as LegacyUser;
use CoopTilleuls\MigrationBundle\Tests\TestBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class LoaderContext implements Context
{
    private $doctrine;
    private $command;
    private $output;
    private $statusCode;

    public function __construct(Registry $doctrine, MigrationLoadCommand $command)
    {
        $this->doctrine = $doctrine;
        $this->command = $command;
        $this->output = new BufferedOutput();
    }

    /**
     * @When I execute user loader
     */
    public function iExecuteUserLoader(): void
    {
        $em = $this->doctrine->getManagerForClass(LegacyUser::class);
        foreach (['admin' => 'password', 'foo' => 'bar', 'john' => 'doe'] as $login => $pswd) {
            $user = new LegacyUser();
            $user->setLogin($login);
            $user->setPswd(sha1($pswd));
            if ('foo' === $login) {
                $user->setDeleted(true);
            }

            $em->persist($user);
        }
        $em->flush();

        $this->statusCode = $this->command->run(new StringInput('user'), $this->output);
    }

    /**
     * @Then users must be imported
     */
    public function usersMustBeImported(): void
    {
        $content = trim(preg_replace('/[ \/]{2,}/', '', $this->output->fetch()));
        Assert::assertEquals(0, $this->statusCode, sprintf("An error occurred on command:\n%s", $content));
        Assert::assertContains('Loading data from loader "user"', $content);
        Assert::assertContains('2 record(s) successfully loaded', $content);
        Assert::assertContains('[OK] Loader "user" successfully executed', $content);

        $em = $this->doctrine->getManagerForClass(User::class);
        $users = $em->getRepository(User::class)->findAll();

        Assert::assertEquals(1, $users[0]->getId());
        Assert::assertEquals(1, $users[0]->getLegacyId());
        Assert::assertEquals('admin', $users[0]->getUsername());
        Assert::assertEquals(sha1('password'), $users[0]->getPassword());

        Assert::assertEquals(2, $users[1]->getId());
        Assert::assertEquals(3, $users[1]->getLegacyId());
        Assert::assertEquals('john', $users[1]->getUsername());
        Assert::assertEquals(sha1('doe'), $users[1]->getPassword());
    }

    /**
     * @When I execute user loader without data
     */
    public function iExecuteUserLoaderWithoutData(): void
    {
        $this->statusCode = $this->command->run(new StringInput('user'), $this->output);
    }

    /**
     * @Then no users must be imported
     */
    public function noUsersMustBeImported(): void
    {
        $content = trim(preg_replace('/[ \/]{2,}/', '', $this->output->fetch()));
        Assert::assertEquals(0, $this->statusCode);
        Assert::assertContains('Loading data from loader "user"', $content);
        Assert::assertContains('No data loaded', $content);
        Assert::assertContains('[OK] Loader "user" successfully executed', $content);

        $em = $this->doctrine->getManagerForClass(User::class);
        Assert::assertCount(0, $em->getRepository(User::class)->findAll());
    }

    /**
     * @When I execute foo loader by its class name
     */
    public function iExecuteFooLoaderByItsClassName(): void
    {
        $this->statusCode = $this->command->run(new StringInput('CoopTilleuls\\\MigrationBundle\\\Tests\\\LegacyBundle\\\Loader\\\FooLoader'), $this->output);
    }

    /**
     * @When I execute foo loader by its alias
     */
    public function iExecuteFooLoaderByItsAlias(): void
    {
        $this->statusCode = $this->command->run(new StringInput('foo'), $this->output);
    }

    /**
     * @Then foo loader must have been executed
     */
    public function fooLoaderMustHaveBeenExecuted(): void
    {
        $content = trim(preg_replace('/[ \/]{2,}/', '', $this->output->fetch()));
        Assert::assertEquals(0, $this->statusCode);
        Assert::assertContains('Loading data from loader', $content);
        Assert::assertContains('3 record(s) successfully loaded', $content);
        Assert::assertContains('successfully executed', $content);
    }
}
