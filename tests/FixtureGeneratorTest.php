<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\FixtureGenerationContext;
use Trappar\AliceGeneratorBundle\FixtureGenerator;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ValidAnnotationTester;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class FixtureGeneratorTest extends FixtureGeneratorTestCase
{
    public function testServiceIsLoadingCorrectly()
    {
        $this->assertInstanceOf(FixtureGenerator::class, $this->fixtureGenerator);
    }

    public function testMultipleEntities()
    {
        $this->runConsole('doctrine:database:drop', ['--force' => true]);
        $this->runConsole('doctrine:schema:create');
        
        // Insert our test data then clear so when we go fetch the test data from the database it will include proxy
        // objects - so we can test that case.
        $user = $this->createTestData();
        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $post = $this->em->getRepository(Post::class)->find(1);

        $yaml = $this->fixtureGenerator->generateYaml($post);

        $this->writeYaml($yaml);
        $this->runConsole('hautelook_alice:doctrine:fixtures:load', ['-n' => true, '--purge-with-truncate' => true]);

        $fixtureGeneratedPost = $this->em->getRepository(Post::class)->find(1);

        $this->assertSame($post, $fixtureGeneratedPost);
    }

    public function testNoRecursion()
    {
        $user = $this->createTestData();

        $yaml = $this->fixtureGenerator->generateYaml($user,
            FixtureGenerationContext::create()->setMaximumRecursion(0)
        );

        $this->assertYamlEquals([
            User::class => [
                'User-1' => [
                    'username' => $user->username,
                    'password' => 'test',
                    'roles'    => ['ROLE_ADMIN']
                ]
            ]
        ], $yaml);
    }

    public function testNothingToSaveInChild()
    {
        $user                  = new User();
        $providerTester        = new ProviderTester();
        $user->providerTester1 = $providerTester;
        $user->providerTester2 = $providerTester;

        $yaml = $this->fixtureGenerator->generateYaml($user);

        $this->assertYamlEquals([
            User::class => [
                'User-1' => [
                    'password' => 'test'
                ]
            ]
        ], $yaml);
    }

    public function testObjectConstraint()
    {
        $user = $this->createTestData();
        $post = $user->posts->first();

        $context = FixtureGenerationContext::create()
            ->addEntityConstraint($post);

        $yaml   = $this->fixtureGenerator->generateYaml($user, $context);
        $parsed = $this->parseYaml($yaml);

        $this->assertArrayNotHasKey('Post-2', $parsed[Post::class]);
    }

    public function testAnnotations()
    {
        $test    = new ValidAnnotationTester();
        $test->fakerValueAsArgs = 'myValue';

        $yaml = $this->fixtureGenerator->generateYaml($test);
        $this->assertYamlEquals([
            ValidAnnotationTester::class => [
                'ValidAnnotationTester-1' => [
                    'data' => 'test',
                    'fakerBasic' => '<test()>',
                    'fakerArgs' => '<test("test", true)>',
                    'fakerClassSelf' => '<test()>',
                    'fakerClassWithMethod' => '<test()>',
                    'fakerClassThreeArgs' => '<test(null, "'.ValidAnnotationTester::class.'", "fakerClassThreeArgs")>',
                    'fakerService' => '<test()>',
                    'fakerValueAsArgs' => '<test("myValue")>'
                ]
            ]
        ], $yaml);
    }

    public function testProviders()
    {
        $test          = new ProviderTester();
        $dateTime      = new \DateTime('Jan 1 1999');
        $object        = new \stdClass();
        $object->value = 'myValue';
        $test->created = $dateTime;
        $test->object  = $object;

        $yaml = $this->fixtureGenerator->generateYaml($test);
        $this->assertYamlEquals([
            ProviderTester::class => [
                'ProviderTester-1' => [
                    'created' => '<(new \DateTime("1999-01-01"))>',
                    'object'  => '<test("myValue")>'
                ]
            ]
        ], $yaml);
    }

    public function testNoProviderForObject()
    {
        $test         = new ProviderTester();
        $test->object = new \Exception();

        $yaml = $this->fixtureGenerator->generateYaml($test);

        $this->assertYamlEquals([
            ProviderTester::class => [
                'ProviderTester-1' => [
                    'object' => null
                ]
            ]
        ], $yaml);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /"toFixture" must exist/
     */
    public function testInvalidProvider()
    {
        $this->fixtureGenerator->addProvider(new self());
    }

    /**
     * @return User
     */
    private function createTestData()
    {
        $user           = new User();
        $user->username = 'testUser';
        $user->password = 'test';
        $user->roles    = ['ROLE_ADMIN'];

        $post1           = new Post();
        $post1->title    = 'How To Do Something';
        $post1->body     = 'Just do it!';
        $post1->postedBy = $user;

        $post2           = new Post();
        $post2->title    = 'Web Development Made Easy';
        $post2->body     = 'Just do it!';
        $post2->postedBy = $user;

        $user->posts->add($post1);
        $user->posts->add($post2);

        return $user;
    }

}