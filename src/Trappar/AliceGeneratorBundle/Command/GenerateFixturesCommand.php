<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Trappar\AliceGenerator\FixtureGenerationContext;
use Trappar\AliceGenerator\FixtureGenerator;

class GenerateFixturesCommand extends ContainerAwareCommand
{
    const DEFAULT_DEPTH = 5;
    const DEFAULT_OUTPUT_PATH_SUFFIX = '/DataFixtures/ORM/generated.yml';
    public static $selectionTypes = [
        'all',
        'id',
        'where'
    ];

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var FixtureGenerator
     */
    private $fixtureGenerator;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $entityAutocomplete;
    /**
     * @var array
     */
    private $bundleNamesWithEntities;
    /**
     * @var Questions
     */
    private $questions;

    public function __construct(
        EntityManager $em,
        KernelInterface $kernel,
        FixtureGenerator $fixtureGenerator,
        Filesystem $filesystem
    )
    {
        $this->em               = $em;
        $this->kernel           = $kernel;
        $this->fixtureGenerator = $fixtureGenerator;
        $this->filesystem       = $filesystem;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('generate:fixtures')
            ->setAliases(['doctrine:fixtures:generate'])
            ->setDescription('Generates fixtures based on Doctrine entities.')
            ->addOption('entities', null, InputOption::VALUE_REQUIRED, 'The entities which fixtures will be generator for')
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Maximum depth to traverse through entities relations', self::DEFAULT_DEPTH)
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Where to write the generated YAML file', $this->getDefaultOutputPath());
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->entityAutocomplete = $this->buildEntityAutocomplete();
        $this->questions          = new Questions($input, $output, $this->getHelperSet());
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $configuredInteractively = !$input->getOption('entities');

        $this->writeSection($output, 'Welcome to the Alice fixture generator');
        $output->writeln('This command helps you generate Alice fixtures based on your Doctrine2 entities.');

        if (!$input->getOption('entities')) {
            $entitiesString = $this->askForEntityConfig($output);
            $input->setOption('entities', $entitiesString);
        }

        if ($configuredInteractively && ($this->isDepthDefault($input) || $this->isOutputPathDefault($input))) {
            // We only need to display this if the user hasn't already specified these options directly
            $this->writeSection($output, 'Output options');
        }

        if ($configuredInteractively && $this->isDepthDefault($input)) {
            $output->writeln(array('', 'How deep should the fixture generator recurse through entities\' relations?'));

            $depth = $this->questions->askForRecursionDepth();
            $input->setOption('depth', $depth);
        }

        if ($configuredInteractively && $this->isOutputPathDefault($input)) {
            while (true) {
                $output->writeln(array('', 'Where should the generated YAML file be written?'));

                $outputPath = $this->questions->askForOutputDirectory($this->getBundlesNamesWithEntities(), $this->getDefaultOutputPath());

                if ($this->filesystem->exists($this->locate($outputPath))) {
                    $output->writeln(array('', 'File already exists, overwrite?'));

                    if (!$this->questions->askIfFileOverwrite()) {
                        continue;
                    }
                }

                $input->setOption('output', $outputPath);
                break;
            }
        }

        if ($configuredInteractively) {
            $this->writeSection($output,
                'Next time you call this command you can use the following options to rerun with the same options as ' .
                'you configured interactively'
            );

            $includedOptions = [
                '--entities="' . $input->getOption('entities') . '"'
            ];
            if (!$this->isDepthDefault($input)) {
                $includedOptions[] = '-d' . $input->getOption('depth');
            }
            if (!$this->isOutputPathDefault($input)) {
                $includedOptions[] = '-o"' . $input->getOption('output') . '"';
            }

            $output->writeln(sprintf(
                'console <comment>%s</comment> <info>%s</info>',
                $this->getName(),
                implode(' ', $includedOptions)
            ));
        }
    }

    private function askForEntityConfig(OutputInterface $output)
    {
        $entities = [];
        while (true) {
            $output->writeln(['', '',
                'Enter an entity which will be used to generate fixtures or keep blank to exit entity selection.',
                'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.'
            ]);

            /** @var ClassMetadata $entityMetadata */
            list($entityAlias, $entityMetadata) = $this->questions->askForEntity($this->em, $this->entityAutocomplete);

            if (!$entityMetadata) {
                if (count($entities) == 0) {
                    $output->writeln(['',
                        '<error>Currently no entities are selected for fixture generation, you must specify at least one.</error>',
                    ]);
                    continue;
                } else {
                    break;
                }
            }

            $entityConfig = [$entityAlias];

            $selectionTypesFormatted = implode(', ', array_map(function ($type) {
                return "<comment>$type</comment>";
            }, self::$selectionTypes));

            $output->writeln(['',
                'Which records will be used for generating entities?',
                '<info>Available selection types:</info> ' . $selectionTypesFormatted
            ]);

            $selectionType = $this->questions->askForSelectionType(self::$selectionTypes);

            $selection = '';
            switch ($selectionType) {
                case 'all':
                    $selection = $selectionType;
                    break;
                case 'id':
                    $output->writeln(['',
                        'Enter a comma separated list of all IDs to include in fixtures for <comment>' . $entityAlias . '</comment>'
                    ]);

                    $ids       = $this->questions->askForIDs();
                    $selection = [$selectionType, $ids];
                    break;
                case 'where':
                    $output->writeln(['',
                        'Enter YAML dictionary of DQL where conditions used to find entities for <comment>' . $entityAlias . '</comment>',
                        sprintf(
                            '<info>Available fields:</info> %s',
                            implode(', ', array_map(function ($field) {
                                return "<comment>$field</comment>";
                            }, $entityMetadata->getFieldNames()))
                        ),
                        '<info>Example:</info> <comment>username:test</comment>, <comment>email:test@email.com</comment>',
                    ]);

                    $where     = $this->questions->askForWhereConditions($entityMetadata);
                    $selection = [$selectionType, $where];
                    break;
            }

            $entityConfig[] = $selection;

            $output->writeln(['',
                'Would you like to add entities selected in this way as object constraints? <info>(see AliceGenerator usage documentation)</info>'
            ]);

            if ($this->questions->askIfAddAsEntityConstraints()) {
                $entityConfig[] = true;
            }

            $entities[] = $entityConfig;
        }

        $entities = array_unique($entities, SORT_REGULAR);

        $entitiesString = Yaml::dump($entities, 0);
        $entitiesString = preg_replace('~^\[|\]$~', '', $entitiesString);

        return $entitiesString;
    }

    /**
     * @see Command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeSection($output, 'Generating Fixture File');

        $entitiesConfig = Yaml::parse('[' . $input->getOption('entities') . ']');

        $entities = [];
        $errors   = [];

        $fixtureGenerationContext = FixtureGenerationContext::create();
        $fixtureGenerationContext->setMaximumRecursion($input->getOption('depth'));

        foreach ($entitiesConfig as $entityConfig) {
            if (!is_array($entityConfig) || count($entityConfig) < 2) {
                $errors[] = $this->getEntitySelectionError('Not formatted correctly', $entityConfig);
                continue;
            }

            list($entityAlias, $selectionType) = $entityConfig;
            $addToEntityConstraints = (isset($entityConfig[2])) ? $entityConfig[2] : false;

            try {
                $repo = $this->em->getRepository($entityAlias);
            } catch (\Exception $e) {
                $errors[] = $this->getEntitySelectionError('Unable to locate repository', $entityConfig);
                continue;
            }

            $results = [];

            $selectionProcessed = true;
            if ($selectionType === 'all') {
                $results = $repo->findAll();

                if (!count($results)) {
                    $errors[] = $this->getEntitySelectionError('No results returned', $entityConfig, false);
                }
            } elseif (is_array($selectionType) && count($selectionType) == 2) {
                if ($selectionType[0] === 'id') {
                    foreach ($selectionType[1] as $id) {
                        try {
                            $result = $repo->find($id);

                            if ($result) {
                                $results[] = $result;
                            } else {
                                $errors[] = $this->getEntitySelectionError("No result returned for ID: $id", $entityConfig, false);
                            }
                        } catch (\Exception $e) {
                            $errors[] = $this->getEntitySelectionError($e->getMessage(), $entityConfig);
                        }
                    }
                } elseif ($selectionType[0] === 'where') {
                    try {
                        $results = $repo->findBy($selectionType[1]);
                    } catch (\Exception $e) {
                        $errors[] = $this->getEntitySelectionError($e->getMessage(), $entityConfig);
                    }
                } else {
                    $selectionProcessed = false;
                }
            } else {
                $selectionProcessed = false;
            }

            if (!$selectionProcessed) {
                $errors[] = $this->getEntitySelectionError("Unknown selection type or format", $entityConfig);
            }

            if ($addToEntityConstraints) {
                foreach ($results as $entity) {
                    $fixtureGenerationContext->addPersistedObjectConstraint($entity);
                }
            }
            $entities = array_merge($entities, $results);
        }

        $yaml = $this->fixtureGenerator->generateYaml($entities, $fixtureGenerationContext);

        $outputFile = $this->locate($input->getOption('output'));

        $output->writeln([
            sprintf(
                'Writing generated fixtures to <info>"%s"</info>',
                $outputFile
            )
        ]);

        $this->writeFile($outputFile, $yaml);

        if (!count($errors)) {
            $this->writeSection($output, 'Written successfully with no errors!');
        } else {
            $this->writeSection($output,
                'There were errors during fixture generation, check the errors below and the generated fixture file for more information.',
                'bg=yellow;fg=black'
            );
            foreach ($errors as $error) {
                $output->writeln(sprintf('<error>%s</error>', $error));
            }
        }
    }

    private function writeFile($outputPath, $contents)
    {
        $outputDirectory = pathinfo($outputPath, PATHINFO_DIRNAME);

        if (!$this->filesystem->exists($outputDirectory)) {
            $this->filesystem->mkdir($outputDirectory);
        }

        $this->filesystem->dumpFile($outputPath, $contents);
    }

    private function buildEntityAutocomplete()
    {
        // Bundles with entities are most likely to be at the end of this array, so reverse it!
        $entities         = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $entityNamespaces = $this->em->getConfiguration()->getEntityNamespaces();

        return array_merge(
            array_keys($entityNamespaces),
            array_filter(array_map(function ($entity) use ($entityNamespaces) {
                foreach ($entityNamespaces as $alias => $entityNamespace) {
                    $shortEntityName = str_replace($entityNamespace . '\\', $alias . ':', $entity);
                    if ($shortEntityName != $entity) {
                        return $shortEntityName;
                    }
                }

                return null;
            }, $entities))
        );
    }

    /**
     * The implementation of this in FileLocator doesn't allow for files which don't exist, so this is a simple
     * reimplementation of that
     *
     * @param $file
     * @return string
     */
    private function locate($file)
    {
        if (isset($file[0]) && '@' === $file[0]) {
            if (false !== strpos($file, '..')) {
                throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $file));
            }

            $bundleName = substr($file, 1);
            $path       = '';
            if (false !== strpos($bundleName, '/')) {
                list($bundleName, $path) = explode('/', $bundleName, 2);
            }

            $bundle = $this->kernel->getBundle($bundleName);
            $file   = $bundle->getPath() . '/' . $path;
        }

        return $file;
    }

    private function isDepthDefault(InputInterface $input)
    {
        return $input->getOption('depth') == self::DEFAULT_DEPTH;
    }

    private function isOutputPathDefault(InputInterface $input)
    {
        return $input->getOption('output') == $this->getDefaultOutputPath();
    }

    private function getBundlesNamesWithEntities()
    {
        if (!$this->bundleNamesWithEntities) {
            $this->bundleNamesWithEntities = array_keys($this->em->getConfiguration()->getEntityNamespaces());
        }

        return $this->bundleNamesWithEntities;
    }

    private function getDefaultOutputPath()
    {
        $bundleNames = $this->getBundlesNamesWithEntities();

        $defaultOutputPath = false;
        if (count($bundleNames) > 0) {
            $suggestedBundle   = $bundleNames[0];
            $defaultOutputPath = '@' . $suggestedBundle . self::DEFAULT_OUTPUT_PATH_SUFFIX;
        }

        return $defaultOutputPath;
    }

    private function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    private function getEntitySelectionError($message, $entityConfig, $displaySkipped = true)
    {
        $parts = [$message];

        if ($displaySkipped) {
            $parts[] = 'Selection skipped';
        }

        return
            sprintf('Selection %s - ', Yaml::dump($entityConfig, 0))
            . implode('. ', array_filter($parts))
            . '.';
    }
}