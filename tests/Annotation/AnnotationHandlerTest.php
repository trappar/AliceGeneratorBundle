<?php

namespace Trappar\AliceGeneratorBundle\Tests\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trappar\AliceGeneratorBundle\Annotation\AnnotationHandler;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;
use Trappar\AliceGeneratorBundle\FixtureGenerator;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Command\GenerateFixturesCommand;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\FooProvider;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;

class AnnotationHandlerTest extends KernelTestCase
{
    /**
     * @var AnnotationHandler
     */
    protected $annotationHandler;

    public function setUp()
    {
        self::bootKernel();
        $this->annotationHandler = static::$kernel->getContainer()->get('trappar_alice_generator.annotation.handler');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must be declared/
     */
    public function testNullDataValue()
    {
        $data = new Fixture\Data();
        $data->value = null;

        $value = $this->annotationHandler->handleDataAnnotation($data, $this->getSampleProperty(), '');
        $this->assertEquals(FixtureGenerator::SKIPVALUE, $value);
    }
    
    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must be declared/
     */
    public function testBadDataValue()
    {
        $data = new Fixture\Data();

        $this->annotationHandler->handleDataAnnotation($data, $this->getSampleProperty(), '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must be declared/
     */
    public function testBadFakerValue()
    {
        $faker = new Fixture\Faker();

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must be an array/
     */
    public function testBadFakerArguments()
    {
        $faker            = new Fixture\Faker();
        $faker->name      = 'test';
        $faker->arguments = 'asdf';

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /valid class/
     */
    public function testBadFakerClass()
    {
        $faker        = new Fixture\Faker();
        $faker->name  = 'test';
        $faker->class = 'string';

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must contain a/
     */
    public function testFakerClassNoToFixtureMethod()
    {
        $faker        = new Fixture\Faker();
        $faker->name  = 'test';
        $faker->class = GenerateFixturesCommand::class;

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /valid service/
     */
    public function testBadFakerService()
    {
        $faker          = new Fixture\Faker();
        $faker->name    = 'test';
        $faker->service = 'string';

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /contain a toFixture method/
     */
    public function testFakerServiceWithNoToFixtureMethod()
    {
        $faker          = new Fixture\Faker();
        $faker->name    = 'test';
        $faker->service = 'service_container';

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /Only one of/
     */
    public function testBadFakerMultipleTypes()
    {
        $faker          = new Fixture\Faker();
        $faker->name    = 'test';
        $faker->service = 'string';
        $faker->class   = 'string';

        $this->annotationHandler->handleFakerAnnotation($faker, $this->getSampleProperty(), '', '');
    }

    public function testFakerOnMethod()
    {
        $method = new \ReflectionMethod(FooProvider::class, 'toFixture');

        $provider = $this->annotationHandler->getProviderFromMethod($method, '', ['test']);

        $this->assertSame('<test("test")>', $provider);
    }
    
    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /Must have @Faker/
     */
    public function testNoFakerOnMethod()
    {
        $method = new \ReflectionMethod(FooProvider::class, 'testMethod1');

        $this->annotationHandler->getProviderFromMethod($method, '', []);
    }
    
    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /not be null/
     */
    public function testFakerOnMethodNoName()
    {
        $method = new \ReflectionMethod(FooProvider::class, 'testMethod2');
        
        $this->annotationHandler->getProviderFromMethod($method, '', []);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /May not have/
     */
    public function testFakerOnMethodInvalidAttribute()
    {
        $method = new \ReflectionMethod(FooProvider::class, 'testMethod3');

        $this->annotationHandler->getProviderFromMethod($method, '', []);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /may not have more than one/
     */
    public function testMultipleAnnotations()
    {
        $property = new \ReflectionProperty(InvalidAnnotations::class, 'whatever');
        
        $this->annotationHandler->handlePropertyAnnotations($property, InvalidAnnotations::class, '');
    }

    protected function getSampleProperty()
    {
        return new \ReflectionProperty(User::class, 'username');
    }
}

class InvalidAnnotations
{
    /**
     * @Fixture\Data("test")
     * @Fixture\Faker("test")
     */
    public $whatever;
}