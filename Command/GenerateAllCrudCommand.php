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
use Flexix\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateAllCrudCommand extends GenerateDoctrineCommand {

    private $generator;

    /**
     * @see Command
     */
    protected function configure() {
        $this
                ->setName('flexix:generate:all:crud')
                ->setAliases(array('flexix:generate:crud'))
                ->setDescription('Generates a CRUD based on a Doctrine entity')
                ->setDefinition(array(
                    new InputArgument('module', InputArgument::REQUIRED, 'Module'),
                    new InputArgument('bundle', InputArgument::REQUIRED, 'Bundle'),
                    new InputArgument('alias', InputArgument::OPTIONAL, 'Alias'),
                    new InputOption('translation', '', InputOption::VALUE_OPTIONAL, 'additional translation language code'),
                ))
                ->setHelp(<<<EOT
The <info>%command.name%</info> command generates all CRUD from bundle based on a Doctrine entity.
<info>php %command.full_name% --alias=some-alias --module=some-module (name of alias from mapper.yml)</info>
EOT
                )
        ;
    }

    protected function confirmGeneration(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output) {
//        if ($input->isInteractive()) {
//            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
//            if (!$questionHelper->ask($input, $output, $question)) {
//                $output->writeln('<error>Command aborted</error>');
//
//                return 1;
//            }
//        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $questionHelper = $this->getQuestionHelper();

        if ($this->confirmGeneration($questionHelper, $input, $output)) {
            return 1;
        }

        $module = $input->getArgument('module');
        $bundle = $input->getArgument('bundle');
        $alias = $input->getArgument('alias');
        $options['translation'] = $input->getOption('translation');

        $this->generate( $module, $bundle,$alias, $options,$output);//->generate();
        $questionHelper->writeGeneratorSummary($output, array());
    }

//    protected function interact(InputInterface $input, OutputInterface $output) {
//
//        
//    }
    
    protected function filterByAlias($classes,$alias)
    {
        
        if($alias)
        {
             foreach ($classes as $map) {
                
                if($map['alias']==$alias)
                {
                    $classes=[];
                    $classes[]=$map;                    
                }
            }
            return $classes;
        }
        else
        {
            return $classes;
        }
        
    }
    
    protected function snakeCase($text) {
   
        $snakeCase = preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $text);
        return ltrim(strtolower($snakeCase), '_');
    }

    protected function generate( $module, $bundle,$alias=null, $options,$output) {

        if (null == $this->generator) {

            $entityMapper = $this->getContainer()->get('flexix_prototype_controller.entity_mapper');

            $mappedBundles = $entityMapper->getBundles();

            $shortBundle=$this->snakeCase(substr($bundle, 0, -6));    
            $classes = $mappedBundles[$shortBundle];

            foreach ($classes as $map) {
                
            //  echo  "\$loader->load('services\\".$module."\\".str_replace('-','_',$map['alias']).".yml');\n";
                
            }
            
            foreach ($classes as $map) {
                
           //   echo  "<a href='./".$module."/".str_replace('-','_',$map['alias'])."/index' >".ucfirst($map['alias'])."</a>\n";
                
            }
            
            $classes=$this->filterByAlias($classes,$alias);
            
            foreach ($classes as $map) {

                if($map['alias']){
                $command = $this->getApplication()->find('flexix:generate:crud');
                $arguments = array(
                    'module'=> $module,
                    'alias' => $map['alias']
                );
                
                
                $arguments['--listView'] = 'yes';
                $arguments['--getView']= 'yes';
                $arguments['--tabsView']= 'yes';
                $arguments['--filter'] = 'yes';
                $arguments['--insert'] = 'yes';
                $arguments['--edit'] = 'yes';
                $arguments['--delete'] = 'yes';
                $arguments['--pagination']= 'yes';
                $arguments['--lazyPagination'] = 'yes';
                $arguments['--customizedModel']= 'yes';
                $arguments['--translation'] = $options['translation'];
                $arguments['--silent'] = true;
                $inputCommand = new ArrayInput($arguments);
                
                
                $returnCode = $command->run($inputCommand, $output);
                
                }
            }
          
        }
    }

}
