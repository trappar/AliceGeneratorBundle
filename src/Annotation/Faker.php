<?php

namespace Trappar\AliceGeneratorBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
final class Faker implements FixtureAnnotationInterface
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var boolean
     */
    public $valueAsArgs;

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