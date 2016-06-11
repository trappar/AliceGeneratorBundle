<?php

namespace Trappar\AliceGeneratorBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Ignore implements FixtureAnnotationInterface
{
}