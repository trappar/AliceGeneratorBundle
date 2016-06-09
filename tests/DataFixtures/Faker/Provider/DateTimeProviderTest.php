<?php

namespace Trappar\AliceGeneratorBundle\Tests\DataFixtures\Faker\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trappar\AliceGeneratorBundle\DataFixtures\Faker\Provider\DateTimeProvider;

class DateTimeProviderTest extends KernelTestCase
{
    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    protected function setUp()
    {
        self::bootKernel();
        $this->dateTimeProvider = static::$kernel->getContainer()->get('faker.provider.datetime');
    }

    /**
     * @cover ::fixture
     */
    public function testFixture()
    {
        $this->assertEquals(
            '<(new \\DateTime(\'2000-12-26\'))>',
            $this->dateTimeProvider->fixture(new \DateTime('Dec 26 2000'))
        );
        
        $this->assertEquals(
            '<(new \\DateTime(\'2016-06-07 02:07:00\'))>',
            $this->dateTimeProvider->fixture(new \DateTime('Jun 7 2016 2:07'))
        );
    }
}