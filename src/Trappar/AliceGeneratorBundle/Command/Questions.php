<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Questions
{
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var HelperSet
     */
    private $helperSet;

    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        $this->input     = $input;
        $this->output    = $output;
        $this->helperSet = $helperSet;
    }

    public function askForRecursionDepth()
    {
        $question = $this->getQuestion('Maximum recursion depth', 5);
        $question->setValidator([Validators::class, 'validateInt']);

        return $this->ask($question);
    }

    public function askForOutputDirectory($bundleNames, $defaultOutputPath)
    {
        $question = $this->getQuestion('Output location', $defaultOutputPath);
        $question->setAutocompleterValues(array_map(function ($bundleName) {
            return "@$bundleName/DataFixtures/ORM/generated.yml";
        }, $bundleNames));
        $question->setValidator([Validators::class, 'validateOutputPath']);

        return $this->ask($question);
    }

    public function askIfFileOverwrite()
    {
        $question = $this->getQuestion('Overwrite', 'yes');
        $question->setAutocompleterValues(['yes', 'no']);
        $question->setValidator([Validators::class, 'validateYesNo']);

        return $this->ask($question);
    }

    public function askForEntity(EntityManager $em, $entityAutocomplete)
    {
        $question = $this->getQuestion('Entity shortcut name', false);
        $question->setAutocompleterValues($entityAutocomplete);
        $question->setValidator(Validators::createBoundValidator('validateEntity', $em));

        return $this->ask($question);
    }

    public function askForSelectionType($selectionTypes)
    {
        $question = $this->getQuestion('Entity selection type', $selectionTypes[0]);
        $question->setAutocompleterValues($selectionTypes);
        $question->setValidator(Validators::createBoundValidator('validateSelectionType', $selectionTypes));

        return $this->ask($question);
    }

    public function askForIDs()
    {
        $question = $this->getQuestion('IDs to include', '1');
        $question->setValidator([Validators::class, 'validateID']);

        return $this->ask($question);
    }

    public function askForWhereConditions(ClassMetadata $entityMetadata)
    {
        $question = $this->getQuestion('Where conditions', false);
        $question->setValidator(Validators::createBoundValidator('validateWhereConditions', $entityMetadata));

        return $this->ask($question);
    }

    public function askIfAddAsEntityConstraints()
    {
        $question = $this->getQuestion('Add as object constraints', 'no');
        $question->setAutocompleterValues(['yes', 'no']);
        $question->setValidator([Validators::class, 'validateYesNo']);

        return $this->ask($question);
    }

    private function getQuestion($question, $default, $sep = ':')
    {
        $questionString = $default
            ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep)
            : sprintf('<info>%s</info>%s ', $question, $sep);

        return new Question($questionString, $default);
    }

    private function ask(Question $question)
    {
        return $this->helperSet->get('question')->ask($this->input, $this->output, $question);
    }
}