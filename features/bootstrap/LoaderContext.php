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
use CoopTilleuls\MigrationBundle\Command\MigrationLoadCommand;
use CoopTilleuls\MigrationBundle\Tests\LegacyBundle\Entity\User as LegacyUser;
use CoopTilleuls\MigrationBundle\Tests\TestBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
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
     * @BeforeScenario
     */
    public function resetDatabase()
    {
        foreach ($this->doctrine->getManagers() as $manager) {
            /** @var EntityManagerInterface $manager */
            $purger = new ORMPurger($manager);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
            try {
                $purger->purge();
            } catch (DBALException $e) {
                $schemaTool = new SchemaTool($manager);
                $schemaTool->createSchema($manager->getMetadataFactory()->getAllMetadata());
            }
        }
    }

    /**
     * @When I execute user loader
     */
    public function iExecuteUserLoader()
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
    public function usersMustBeImported()
    {
        $content = trim(preg_replace('/[ ]{2,}/', '', $this->output->fetch()));
        \PHPUnit_Framework_Assert::assertEquals(0, $this->statusCode, sprintf("An error occurred on command:\n%s", $content));
        \PHPUnit_Framework_Assert::assertEquals(<<<'EOF'
Loading data from loader "user"
===============================

 2 record(s) successfully loaded

 [OK] Loader "user" successfully executed
EOF
            , $content);

        $em = $this->doctrine->getManagerForClass(User::class);
        $users = $em->getRepository(User::class)->findAll();

        \PHPUnit_Framework_Assert::assertEquals(1, $users[0]->getId());
        \PHPUnit_Framework_Assert::assertEquals(1, $users[0]->getLegacyId());
        \PHPUnit_Framework_Assert::assertEquals('admin', $users[0]->getUsername());
        \PHPUnit_Framework_Assert::assertEquals(sha1('password'), $users[0]->getPassword());

        \PHPUnit_Framework_Assert::assertEquals(2, $users[1]->getId());
        \PHPUnit_Framework_Assert::assertEquals(3, $users[1]->getLegacyId());
        \PHPUnit_Framework_Assert::assertEquals('john', $users[1]->getUsername());
        \PHPUnit_Framework_Assert::assertEquals(sha1('doe'), $users[1]->getPassword());
    }

    /**
     * @When I execute user loader without data
     */
    public function iExecuteUserLoaderWithoutData()
    {
        $this->statusCode = $this->command->run(new StringInput('user'), $this->output);
    }

    /**
     * @Then no users must be imported
     */
    public function noUsersMustBeImported()
    {
        \PHPUnit_Framework_Assert::assertEquals(0, $this->statusCode);
        \PHPUnit_Framework_Assert::assertEquals(<<<'EOF'
Loading data from loader "user"
===============================

 No data loaded

 [OK] Loader "user" successfully executed
EOF
            , trim(preg_replace('/[ ]{2,}/', '', $this->output->fetch())));

        $em = $this->doctrine->getManagerForClass(User::class);
        \PHPUnit_Framework_Assert::assertCount(0, $em->getRepository(User::class)->findAll());
    }
}
