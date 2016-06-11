<?php

namespace Trappar\AliceGeneratorBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Faker implements FixtureAnnotationInterface
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $arguments;

    /**
     * @var string
     */
    public $service;

    /**
     * @var string
     */
    public $class;
}