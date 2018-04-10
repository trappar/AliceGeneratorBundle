<?php

namespace Trappar\AliceGeneratorBundle\Tests\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Trappar\AliceGenerator\FixtureGenerationContext;
use Trappar\AliceGenerator\FixtureGenerator;
use Trappar\AliceGeneratorBundle\Command\GenerateFixturesCommand;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;

class GenerateFixturesCommandTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function setUp()
    {
        self::bootKernel();

        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);
        $this->doctrine = static::$kernel->getContainer()->get('doctrine');
    }

    /**
     * @dataProvider getInteractiveCommandData
     * @param Options $options
     */
    public function testCommand(Options $options)
    {
        if ($options->generator === false) {
            $options->generator = $this->createMockGenerator();
        }
        if ($options->filesystem === false) {
            $options->filesystem = $this->createMockFilesystem();
        }
        if ($options->entities === false) {
            $options->entities = $this->getTestEntities();
        }
        if ($options->input) {
            for ($i=0; $i<20; $i++) {
                $options->input[] = '';
            }
        }

        $this->prepareDatabase($options->entities);

        if ($options->exception) {
            $this->expectException($options->exception);
        }
        if ($options->exceptionRegex) {
            $this->expectExceptionMessageRegExp($options->exceptionRegex);
        }

        $display = $this->getOutputFromCommandForInput($options);

        if ($options->displayRegex) {
            $this->assertRegExp($options->displayRegex, $display);
        }
        if ($options->noDisplayRegex) {
            $this->assertNotRegExp($options->noDisplayRegex, $display);
        }
        if (!$options->anyAssertionUsed()) {
            $this->assertRegExp('~Written successfully with no errors\!~', $display);
        }
    }

    public function getInteractiveCommandData()
    {
        $data = [];

        /** VALIDATION SPECIFIC TESTS **/
        // Invalid selection type
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'asdf'];
        $options->displayRegex = '~invalid selection type~i';
        $data[]                = [$options];

        // Invalid ID/integer
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'id', 'abc'];
        $options->displayRegex = '~invalid non-int given~i';
        $data[]                = [$options];

        // ID list with extra comma
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'id', '1,,,2'];
        $options->displayRegex = '~id, \[1, 2\]~i';
        $data[]                = [$options];

        // Invalid where conditions
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'where', 'test', 'username: test'];
        $options->displayRegex = '~malformed inline yaml string~i';
        $data[]                = [$options];

        // Where condition returned empty
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'where', '', 'username: test'];
        $options->displayRegex = '~at least one condition~i';
        $data[]                = [$options];

        // Where condition with non-existent field
        $options               = new Options();
        $options->input        = ['TestBundle:User', 'where', 'test: test', 'username: test'];
        $options->displayRegex = '~unknown field~i';
        $data[]                = [$options];

        // Attempt yes/no with garbage
        $options               = new Options();
        $options->input        = ['TestBundle:User', '', 'asdf'];
        $options->displayRegex = '~"yes" or "no"~i';
        $data[]                = [$options];

        // Output path with wrong extension
        $options               = new Options();
        $options->input        = ['TestBundle:User', '', '', '', '', 'test.exe'];
        $options->displayRegex = '~must have .yml extension~i';
        $data[]                = [$options];


        /** TESTS WITH NO ERRORS **/
        // Selecting two posts by IDs
        $fg = $this->createMockGenerator();
        $fg->method('generateYaml')->with($this->logicalNot($this->arrayHasKey(2)));
        $options            = new Options();
        $options->generator = $fg;
        $options->input     = ['TestBundle:Post', 'id', '1,2'];
        $data[]             = [$options];

        // Selecting a post by where
        $fg = $this->createMockGenerator();
        $fg->method('generateYaml')->with($this->logicalNot($this->arrayHasKey(1)));
        $options            = new Options();
        $options->generator = $fg;
        $options->input     = ['TestBundle:Post', 'where', 'title: My Title'];
        $data[]             = [$options];

        // Overwrite existing file
        $options               = new Options();
        $options->filesystem   = $this->createMockFilesystem(true);
        $options->input        = ['TestBundle:User', '', '', '', '', '', 'no'];
        $options->displayRegex = '~file already exists~i';
        $data[]                = [$options];

        // Ensure that "text time" command displays custom output options
        $options               = new Options();
        $options->options      = ['-d' => 1, '-o' => 'test.yml'];
        $options->input        = ['TestBundle:User'];
        $options->displayRegex = '~-d.*-o~';
        $data[]                = [$options];

        // Depth and output location are at default so the "next time" command should not display those options
        $options                 = new Options();
        $options->input          = ['TestBundle:User'];
        $options->noDisplayRegex = '~-d|-o~';
        $data[]                  = [$options];

        // Ensure duplicate configuration doesn't result in duplicate selection
        $options               = new Options();
        $options->input        = ['TestBundle:User', '', '', 'TestBundle:User'];
        $options->displayRegex = '~--entities="\[\'TestBundle:User\', all\]"~';
        $data[]                = [$options];

        // Ensure that FixtureGenerationContext is being generated correctly
        $entities = $this->getTestEntities();
        list($post1, $post2) = $entities;
        $fg = $this->createMockGenerator();
        $fg->method('generateYaml')
            ->with($this->anything(), $this->callback(
                function (FixtureGenerationContext $context) use ($post1, $post2) {
                    return $context->getPersistedObjectConstraints()->checkValid($post1) === true
                    && $context->getPersistedObjectConstraints()->checkValid($post2) === false
                    && $context->getMaximumRecursion() === 100;
                }
            ));
        $options            = new Options();
        $options->generator = $fg;
        $options->input     = ['TestBundle:Post', 'id', '', 'yes'];
        $options->entities  = $entities;
        $options->options   = ['-d' => 100];
        $data[]             = [$options];


        /** TESTS WITH ERROR OUTPUT **/
        // Attempt not to select anything
        $options               = new Options();
        $options->input        = ['', 'TestBundle:User'];
        $options->displayRegex = '~no entities are selected for fixture generation~i';
        $data[]                = [$options];

        // Attempt invalid where conditions
        $options               = new Options();
        $options->options      = ['--entities' => "['TestBundle:User', [where, { nonexistent: test }]]"];
        $options->displayRegex = '~unrecognized field: nonexistent~i';
        $data[]                = [$options];

        // Attempt to select records by invalid ID
        $options               = new Options();
        $options->options      = ['--entities' => "['TestBundle:User', [id, [null]]]"];
        $options->displayRegex = '~The identifier id is missing~';
        $data[]                = [$options];

        // Selecting a non-existent post by ID
        $fg = $this->createMockGenerator();
        $fg->method('generateYaml')->with($this->logicalNot($this->arrayHasKey(0)));
        $options               = new Options();
        $options->generator    = $fg;
        $options->displayRegex = '~no result returned~i';
        $options->input        = ['TestBundle:Post', 'id', '3'];
        $data[]                = [$options];

        // Attempt to save to an invalid location
        $options                 = new Options();
        $options->exception      = \RuntimeException::class;
        $options->exceptionRegex = '~invalid characters~';
        $options->options        = [
            '--entities' => '"[\'TestBundle:User\', all]"',
            '-o'         => '@../blah.yml'
        ];
        $data[]                  = [$options];

        // Attempt to select non-existent entity
        $options               = new Options();
        $options->displayRegex = '~Unable to locate repository~';
        $options->options      = ['--entities' => "['TestBundle:BadEntity', all]"];
        $data[]                = [$options];

        // Attempt to select all records for entity with no records
        $options               = new Options();
        $options->displayRegex = '~no results returned~i';
        $options->options      = ['--entities' => "['TestBundle:User', all]"];
        $options->entities     = [];
        $data[]                = [$options];

        // Attempt to select entities with selection type is missing
        $options               = new Options();
        $options->displayRegex = '~not formatted correctly~i';
        //
        $options->options = ['--entities' => "['TestBundle:User']"];
        $data[]           = [$options];

        // Attempt to select entities with malformed yaml - should be an array
        $options               = new Options();
        $options->displayRegex = '~not formatted correctly~i';
        $options->options      = ['--entities' => "'TestBundle:User', all"];
        $data[]                = [$options];

        // Invalid entity name
        $options               = new Options();
        $options->displayRegex = '~Unable to fetch entity information for "badEntity"~';
        $options->input        = ['badEntity', 'TestBundle:Post'];
        $data[]                = [$options];

        // Invalid selection type
        $options               = new Options();
        $options->displayRegex = '~unknown selection type~i';
        $options->options      = ['--entities' => "['TestBundle:User', asdf]"];
        $data[]                = [$options];

        // Invalid selection type
        $options               = new Options();
        $options->displayRegex = '~unknown selection type~i';
        $options->options      = ['--entities' => "['TestBundle:User', [sadf]]"];
        $data[]                = [$options];

        return $data;
    }

    private function getOutputFromCommandForInput(Options $options) //$input, FixtureGenerator $fixtureGenerator, Filesystem $filesystem, array $options = [])
    {
        $generateFixturesCommand = new GenerateFixturesCommand($this->doctrine, static::$kernel, $options->generator, $options->filesystem);

        $application = new \Symfony\Component\Console\Application();
        $application->add($generateFixturesCommand);

        $command = $application->find('generate:fixtures');
        $commandTester = new CommandTester($command);

        if (method_exists(CommandTester::class, 'setInputs')) {
            $commandTester->setInputs($options->input);
        } else {
            $stream = fopen('php://memory', 'r+', false);
            fwrite($stream, implode("\n", $options->input));
            rewind($stream);

            $helper  = $command->getHelper('question');
            $helper->setInputStream($stream);
        }

        $commandTester->execute(array_merge(['command' => $command->getName()], $options->options));

        return $commandTester->getDisplay(true);
    }

    private function createMockFilesystem($fileExists = false)
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->any())
            ->method('exists')
            ->willReturn($fileExists);
        $filesystem
            ->expects($this->any())
            ->method('dumpFile')
            ->with($this->isType('string'), 'test')
            ->willReturn(true);

        return $filesystem;
    }

    private function createMockGenerator()
    {
        $fixtureGenerator = $this->createMock(FixtureGenerator::class);
        $fixtureGenerator
            ->expects($this->any())
            ->method('generateYaml')
            ->will($this->returnValue('test'));

        return $fixtureGenerator;
    }

    private function getTestEntities()
    {
        $post        = new Post();
        $post->body  = 'Test this';
        $post->title = 'My Title';

        $post2        = new Post();
        $post2->title = 'blah';
        $post2->body  = 'test';

        $user           = new User();
        $user->username = 'test';

        $post->postedBy  = $user;
        $post2->postedBy = $user;

        return [$post, $post2, $user];
    }

    private function prepareDatabase($entities)
    {
        $this->runConsole('doctrine:database:drop', ['--force' => true]);
        $this->runConsole('doctrine:schema:create');

        $em = $this->doctrine->getManager();
        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->flush();
    }

    /**
     * Helper to run a Symfony command.
     *
     * @param string $command
     * @param array  $options
     *
     * @return int
     *
     * @throws \Exception
     */
    private function runConsole($command, array $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options       = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

}

class Options
{
    public $exception = false;
    public $exceptionRegex = false;
    public $displayRegex = false;
    public $noDisplayRegex = false;
    public $input = [];
    public $options = [];
    /** @var bool|FixtureGenerator */
    public $generator = false;
    /** @var bool|Filesystem */
    public $filesystem = false;
    public $entities = false;

    public function anyAssertionUsed()
    {
        return $this->exception || $this->exceptionRegex || $this->displayRegex || $this->noDisplayRegex;
    }
}