<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flexix\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Flexix\GeneratorBundle\Command\Helper\QuestionHelper;
use Flexix\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Flexix\GeneratorBundle\Generator\DirBuilder;
use Flexix\GeneratorBundle\Generator\TranslationsBuilder;
use Flexix\EntityAnalyzerBundle\Util\EntityAnalyzer;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineCrudCommand extends GenerateDoctrineCommand {

    private $generator;

    /**
     * @see Command
     */
    protected function configure() {
        $this
                ->setName('flexix:generate:crud')
                ->setAliases(array('flexix:generate:crud'))
                ->setDescription('Generates a CRUD based on a Doctrine entity')
                ->setDefinition(array(
                    new InputArgument('alias', InputArgument::REQUIRED, 'Alias of class (mapper.yml)'),
                    new InputArgument('module', InputArgument::REQUIRED, 'Module'),
                    new InputOption('locale', '', InputOption::VALUE_REQUIRED, 'Locale', 'en_EN'),
                    new InputOption('listView', '', InputOption::VALUE_OPTIONAL, 'List view'),
                    new InputOption('getView', '', InputOption::VALUE_OPTIONAL, 'Get view'),
                    new InputOption('tabsView', '', InputOption::VALUE_OPTIONAL, 'Lazy pagination'),
                    new InputOption('filter', '', InputOption::VALUE_OPTIONAL, 'Filter'),
                    new InputOption('insert', '', InputOption::VALUE_OPTIONAL, 'Insert'),
                    new InputOption('edit', '', InputOption::VALUE_OPTIONAL, 'Edit'),
                    new InputOption('delete', '', InputOption::VALUE_OPTIONAL, 'Delete'),
                    new InputOption('pagination', '', InputOption::VALUE_OPTIONAL, 'Pagination'),
                    new InputOption('translation', '', InputOption::VALUE_OPTIONAL, 'additional translation language code'),
                    new InputOption('lazyPagination', '', InputOption::VALUE_OPTIONAL, 'Lazy pagination'),
                    new InputOption('customizedModel', '', InputOption::VALUE_OPTIONAL, 'Customized model'),
                    new InputOption('silent', '', InputOption::VALUE_NONE, 'silent')
                ))
                ->setHelp(<<<EOT
The <info>%command.name%</info> command generates a CRUD based on a Doctrine entity.
<info>php %command.full_name% --alias=some-alias --module=some-module (name of alias from mapper.yml)</info>
EOT
                )
        ;
    }

    protected function confirmGeneration(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output) {
        if ($input->isInteractive() && !$input->getOption('silent')) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        
        
        $questionHelper = $this->getQuestionHelper();

        if ($this->confirmGeneration($questionHelper, $input, $output)) {
            return 1;
        }


        $alias = $input->getArgument('alias');
        $module = $input->getArgument('module');
        $output->writeln('<info>' . $module.'\\'.$alias. '</info>');
        
        $options['listView'] = $input->getOption('listView');
        $options['getView'] = $input->getOption('getView');
        $options['tabsView'] = $input->getOption('getView');
        $options['filter'] = $input->getOption('filter');
        $options['insert'] = $input->getOption('insert');
        $options['edit'] = $input->getOption('edit');
        $options['delete'] = $input->getOption('delete');
        $options['pagination'] = $input->getOption('pagination');
        $options['lazyPagination'] = $input->getOption('lazyPagination');
        $options['customizedModel'] = $input->getOption('customizedModel');
        $options['locale'] = $input->getOption('locale');
        $options['translation'] = $input->getOption('translation');


        $this->getGenerator($alias, $module, $options)->generate();
        if (!$input->getOption('silent')) {
            $questionHelper->writeGeneratorSummary($output, array());
        }
    }

    protected function ask($text, $response, $optionName, $input, $output) {


        $question = new Question($this->getQuestionHelper()->getQuestion($text, $response, '?'), $response);
        $question->setValidator(array('Flexix\GeneratorBundle\Command\Validators', 'validateYesNo'));
        $response = $this->getQuestionHelper()->ask($input, $output, $question);

        if (in_array($response, ['no', 'n', false])) {
            $decision = false;
        } else {
            $decision = true;
        }

        $input->setOption($optionName, $decision);
        return $response;
    }

    protected function askQuestions(InputInterface $input, OutputInterface $output) {


        $this->getQuestionHelper()->writeSection($output, "Welcome to the Flexix CRUD generator");

        $getViewQuestion = "\nShould create a view for the single object (get)";
        $getView=$this->ask($getViewQuestion, 'yes', 'getView', $input, $output);
        
        if ($getView == 'yes') {

                $question6 = 'Should create a tabs view';
                $pagination = $this->ask($question6, 'yes', 'lazyPagination', $input, $output);
        }
        
        $listViewQuestion = 'Should create a list View';
        $listView = $this->ask($listViewQuestion, 'yes', 'listView', $input, $output);

        if ($listView) {
            $question1 = 'Should the list view contains filter';
            $this->ask($question1, 'yes', 'filter', $input, $output);

            $question2 = 'Should the list view contains an insert form';
            $this->ask($question2, 'yes', 'insert', $input, $output);

            $question3 = 'Should the list view contains an edit form';
            $this->ask($question3, 'yes', 'edit', $input, $output);

            $question4 = 'Should the list view contains a delete function';
            $this->ask($question4, 'yes', 'delete', $input, $output);

            $question5 = 'Should the list view contains a pagination';
            $pagination = $this->ask($question5, 'yes', 'pagination', $input, $output);

            if ($pagination == 'yes') {

                $question6 = 'Should the pagination ba a lazy pagination';
                $pagination = $this->ask($question6, 'yes', 'lazyPagination', $input, $output);
            }
        }




        $question7 = 'Should configuration contains a customized model';
        $pagination = $this->ask($question7, 'yes', 'customizedModel', $input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

        if ($input->isInteractive() && !$input->getOption('silent')) {

            $this->askQuestions($input, $output);
        }
    }

    protected function getGenerator($alias, $module, $options) {

        // if (null == $this->generator) {

        $entityMapper = $this->getContainer()->get('flexix_prototype_controller.entity_mapper');
        $entityAnalyzerFactory=$this->getContainer()->get('flexix.entity_analyzer.entity_analyzer_factory');
        $entityClass = $entityMapper->getEntityClass($alias);
        $dirBuilder = new DirBuilder($this->getContainer()->getParameter('kernel.root_dir'), $alias, $module, $entityClass);
        $entityAnalyzer = $this->getEntityAnalyzer($entityClass, $dirBuilder,$module);
        $translations = new TranslationsBuilder($dirBuilder->getTranslationsDir(), 'en', $module, $alias, $entityAnalyzer->getProperties(),$entityAnalyzer->getEntity());
        $translations->addTranslations();

        if ($options['translation']) {

            $additionalTranslations = new TranslationsBuilder($dirBuilder->getTranslationsDir(), $options['translation'], $module, $alias, $entityAnalyzer->getProperties(),$entityAnalyzer->getEntity());
            $additionalTranslations->addTranslations();
        }

        $this->generator = new DoctrineCrudGenerator(
                $this->getContainer()->get('filesystem'), $entityClass, $entityAnalyzer, $dirBuilder, $alias, $module, $options,$entityAnalyzerFactory, $this->getContainer()->get('flexix_checlsum.util.checksum_controller'),$this->getContainer()->get('flexix_checlsum.util.dir_builder')
        );
        $bundle = $this->getContainer()->get('kernel')->getBundle(str_replace('\\', '', $dirBuilder->getBundle()));
        $this->generator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        // }

        return $this->generator;
    }

    protected function getAnalyzeFileName($entityClass, $dirBuilder,$module) {
        $arr = explode("\Entity\\", $entityClass);
        return $dirBuilder->getSrcDir() . DIRECTORY_SEPARATOR . $arr[0] . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'entityAnalyze' .DIRECTORY_SEPARATOR.$module. DIRECTORY_SEPARATOR . $arr[1] . '.orm.yml';
    }

    protected function getEntityAnalyzer($entityClass, $dirBuidler,$module) {
        $entityAnalyzerFile = $this->getAnalyzeFileName($entityClass, $dirBuidler,$module);
        return new EntityAnalyzer($entityAnalyzerFile, $entityClass);
    }

    protected function getFormGenerator($bundle = null) {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    public function setFormGenerator(DoctrineFormGenerator $formGenerator) {
        $this->formGenerator = $formGenerator;
    }

}
