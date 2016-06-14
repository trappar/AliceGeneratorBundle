<?php

namespace Trappar\AliceGeneratorBundle\Tests\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;
use Trappar\AliceGeneratorBundle\Annotation\AnnotationHandler;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\InvalidAnnotationTester;

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
    public function testInvalidDataValue()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidDataValue'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /not be null/
     */
    public function testInvalidFakerName()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerName'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /valid class/
     */
    public function testInvalidFakerClass()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerClass'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /must contain a/
     */
    public function testInvalidFakerClassNoToFixtureMethod()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerClassNoToFixtureMethod'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /got string instead of expected array/
     */
    public function testInvalidFakerReturnsNonArray()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerReturnsNonArray'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /valid service/
     */
    public function testInvalidFakerService()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerService'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /contain a toFixture method/
     */
    public function testInvalidFakerServiceNoToFixtureMethod()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerServiceNoToFixtureMethod'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /Only one of/
     */
    public function testInvalidFakerMultipleAttributes()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidFakerMultipleAttributes'),
            '', '', ''
        );
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /may not have more than one/
     */
    public function testInvalidMultipleAnnotations()
    {
        $this->annotationHandler->handlePropertyAnnotations(
            new \ReflectionProperty(InvalidAnnotationTester::class, 'invalidMultipleAnnotations'),
            '', '', ''
        );
    }

    public function testFakerOnMethod()
    {
        $this->assertSame(
            '<test()>',
            $this->annotationHandler->createProviderFromMethod(
                new \ReflectionMethod(InvalidAnnotationTester::class, 'validFakerOnMethod'),
                []
            )
        );
    }


    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /Must have @Faker/
     */
    public function testInvalidFakerOnMethodNoAnnotation()
    {
        $this->annotationHandler->createProviderFromMethod(
            new \ReflectionMethod(InvalidAnnotationTester::class, 'invalidFakerOnMethodNoAnnotation'),
            []
        );
    }
    
    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessageRegExp /May not have/
     */
    public function testInvalidFakerOnMethodAttribute()
    {
        $this->annotationHandler->createProviderFromMethod(
            new \ReflectionMethod(InvalidAnnotationTester::class, 'invalidFakerOnMethodAttribute'),
            []
        );
    }
}