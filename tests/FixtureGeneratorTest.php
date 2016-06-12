<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\FixtureGenerationContext;
use Trappar\AliceGeneratorBundle\FixtureGenerator;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\NoArgumentProvider;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\AnnotationTester;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class FixtureGeneratorTest extends FixtureGeneratorTestCase
{
    public function testServiceIsLoadingCorrectly()
    {
        $this->assertInstanceOf(FixtureGenerator::class, $this->fixtureGenerator);
    }

    public function testMultipleEntities()
    {
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
                    'username' => $user->getUsername(),
                    'password' => 'test',
                    'roles'    => ['ROLE_ADMIN']
                ]
            ]
        ], $yaml);
    }
    
    public function testNothingToSaveInChild()
    {
        $user = new User();
        $providerTester = new ProviderTester();
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
        $post = $user->getPosts()->first();

        $context = FixtureGenerationContext::create()
            ->addEntityConstraint($post);

        $yaml   = $this->fixtureGenerator->generateYaml($user, $context);
        $parsed = $this->parseYaml($yaml);

        $this->assertArrayNotHasKey('Post-2', $parsed[Post::class]);
    }

    public function testAnnotations()
    {
        $test    = new AnnotationTester();
        $test->f = 'fValue';

        $yaml = $this->fixtureGenerator->generateYaml($test);
        $this->assertYamlEquals([
            AnnotationTester::class => [
                'AnnotationTester-1' => [
                    'a' => 'test',
                    'b' => '<test()>',
                    'c' => '<test("test", true)>',
                    'd' => '<test("blah", 1, true)>',
                    'e' => '<test("blah", 1, true)>',
                    'f' => '<test("fValue")>'
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
                    'object' => '<test("myValue")>'
                ]
            ]
        ], $yaml);
    }
    
    public function testNoProviderForObject()
    {
        $test = new ProviderTester();
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /must contain exactly one argument/
     */
    public function testNoArgumentProvider()
    {
        $this->fixtureGenerator->addProvider(new NoArgumentProvider());

        $test = new ProviderTester();
        $test->object = new \Exception();
        
        $this->fixtureGenerator->generateYaml($test);
    }

    /**
     * @return User
     */
    private function createTestData()
    {
        $user = new User();
        $user->setUsername('testUser');
        $user->setPassword('test');
        $user->setRoles(['ROLE_ADMIN']);

        $post1 = new Post();
        $post1->setTitle('How To Do Something')
            ->setBody('Just do it!')
            ->setPostedBy($user);

        $post2 = new Post();
        $post2->setTitle('Web Development Made Easy')
            ->setBody('Just do it!')
            ->setPostedBy($user);

        $user->getPosts()->add($post1);
        $user->getPosts()->add($post2);

        return $user;
    }

}