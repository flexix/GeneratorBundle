<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flexix\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Flexix\GeneratorBundle\Generator\DirBuilder;
use Doctrine\Common\Inflector\Inflector;
use Flexix\GeneratorBundle\Manipulator\ExtensionManipulator;

/**
 * Generates a CRUD controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineCrudGenerator extends Generator
{

    protected $entityAnalyzer;
    protected $entityAnalyzerFactory;
    protected $filesystem;
    protected $dirBuilder;
    protected $entitySingularized;
    protected $entityPluralized;
    protected $alias;
    protected $module;
    protected $options;
    protected $entityClass;
    protected $checksumController;
    protected $checksumControllerDirBuidler;

    /**
     * @param Filesystem $filesystem
     * @param string     $rootDir
     */
    public function __construct(Filesystem $filesystem, $entityClass, $entityAnalyzer, DirBuilder $dirBuilder, $alias, $module, $options, $entityAnalyzerFactory, $checksumController, $checksumControllerDirBuidler)
    {

        $this->filesystem = $filesystem;
        $this->dirBuilder = $dirBuilder;
        $this->alias = $alias;
        $this->module = $module;
        $this->options = $options;
        $this->entityClass = $entityClass;
        $this->entityAnalyzer = $entityAnalyzer;
        $this->entityAnalyzerFactory = $entityAnalyzerFactory;
        $this->checksumController = $checksumController;
        $this->checksumControllerDirBuidler=$checksumControllerDirBuidler;
    }

    protected function getEntityName($entityClass)
    {
        $entity = str_replace('\\', '/', $entityClass);
        $entityParts = explode('/', $entity);
        return end($entityParts);
    }

    protected function getNamespace($entityClass)
    {
        $entity = str_replace('\\', '/', $entityClass);
        $entityParts = explode('/', $entity);
        unset($entityParts[count($entityParts) - 1]);
        return implode('\\', $entityParts);
    }

    protected function getEntitySingularized()
    {
        if (!$this->entitySingularized) {
            $entityName = $this->getEntityName($this->entityClass);
            $this->entitySingularized = lcfirst(Inflector::singularize($entityName));
        }
        return $this->entitySingularized;
    }

    protected function getEntityPluralized()
    {
        if (!$this->entityPluralized) {
            $entityName = $this->getEntityName($this->entityClass);
            $this->entityPluralized = lcfirst(Inflector::pluralize($entityName));
        }
        return $this->entityPluralized;
    }

    public function generate()
    {

        $serviceFilePath = $this->generateServiceConfiguration();
//$this->addServiceToExtensionClass();

        if ($this->getOptions()['getView']) {
            $this->generateGetView();
        }

        if ($this->getOptions()['listView']) {
            $this->generateIndexView();
            $this->generateListView();
        }

        if ($this->getOptions()['tabsView']) {

            $this->generateTabsView();
        }

        if ($this->getOptions()['insert']) {
            $this->generateFormType('Insert');
        }

        if ($this->getOptions()['edit']) {
            $this->generateFormType('Edit');
        }

        if ($this->getOptions()['insert']) {
            $this->generateInsertView();
        }

        if ($this->getOptions()['delete']) {
            $this->generateDeleteView();
        }


        if ($this->getOptions()['edit']) {
            $this->generateEditView();
        }

        if ($this->getOptions()['filter']) {

            $this->generateFilterView();
            $this->generateFilterFormType();
            $this->generateTypeaheadModel();
        }

        if ($this->getOptions()['customizedModel']) {

            $this->generateCustomizedModel();
            $this->generateCustomizedRepository();
            $this->generateCustomizedPaginatorModel();
            $this->generateModelTests();
        }



        $this->generateSmokeTests();
    }

    protected function getModelSuffix($servicePath)
    {
        if ($this->getOptions()['customizedModel']) {
            return $servicePath;
        } else {
            return 'flexix_model';
        }
    }

    public function generateServiceConfiguration()
    {

        $servicePathBundleName = str_replace('\_', '.', $this->snakeCase($this->getDirBuilder()->getBundle()));
        $servicPathAlias = $this->snakeCase($this->getDirBuilder()->toNamespaceName($this->getAlias()));
        $servicePath = sprintf('%s.%s_%s', $servicePathBundleName, $this->getModule(), $servicPathAlias);
        $modelSuffix = $this->getModelSuffix($servicePath);

        $alias = $this->getAlias();
        $module = $this->getDirBuilder()->toNamespaceName($this->getModule());
        $upperAlias = $this->getDirBuilder()->toNamespaceName(ucfirst($alias));
        $upperModule = ucfirst($module);
        $fileName = sprintf('%s/%s.yml', $this->getDirBuilder()->getServiceDir(), $this->snakeCase($this->getDirBuilder()->toNamespaceName($this->getAlias())));
  
        $this->renderFile('crud/service/service.yml.twig', $fileName, array(
            'alias' => $alias,
            'module' => lcfirst($module),
            'options' => $this->getOptions(),
            'template_path' => sprintf('%s\%s', ucfirst($this->getModule()), $this->getDirBuilder()->toNamespaceName($this->getAlias())),
            'service_path' => $servicePath,
            'model_suffix' => $modelSuffix,
            'form_path' => sprintf('%s\\Form\\Type\\%s\\%s\\', $this->getDirBuilder()->getBundle(), $upperModule, $upperAlias),
            'model_dir' => sprintf('%s\\Model\\%s\\%s\\', $this->getDirBuilder()->getBundle(), $upperModule, $upperAlias),
            'repository_dir' => sprintf('%s\\Repository\\%s\\%s\\', $this->getDirBuilder()->getBundle(), $upperModule, $upperAlias),
            'entity_analyzer' => $this->getEntityAnalyzer(),
            'template_var' => $this->getEntitySingularized(),
            'plural_template_var' => $this->getEntityPluralized(),
            'entity_analyzer_factory' => $this->entityAnalyzerFactory
        ));
        return $fileName;
    }

    protected function snakeCase($text)
    {
//   str_replace('//', '_', strtolower();
        $snakeCase = preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $text);
        return ltrim(strtolower($snakeCase), '_');
    }

    protected function renderView($name)
    {
        return $this->renderObject('views', 'html.twig', $this->getDirBuilder()->getViewDir(), $this->snakeCase($name));
    }

    protected function renderTest($name)
    {
        return $this->renderObject('tests', 'php', $this->getDirBuilder()->getTestDir(), $name);
    }

    protected function renderForm($name)
    {
        return $this->renderObject('form', 'php', $this->getDirBuilder()->getFormDir(), $name);
    }

    protected function renderModel($name)
    {
        return $this->renderObject('model', 'php', $this->getDirBuilder()->getModelDir(), $name);
    }

    protected function renderRepository($name)
    {

        return $this->renderObject('repository', 'php', $this->getDirBuilder()->getRepositoryDir(), $name);
    }

    protected function getTranslationPrefix()
    {

        return str_replace('-', '_', sprintf('%s.%s', $this->snakeCase($this->getModule()), $this->snakeCase($this->getAlias())));
    }

    protected function renderObject($templateDir, $type, $resultFileDir, $name)
    {
        $filePath = sprintf('%s/%s.%s', $resultFileDir, $name, $type);
        $templatePath = sprintf('crud/%s/%s.%s.twig', $templateDir, $name, $type);
        $this->renderFile($templatePath, $filePath, array(
//'alias' => $this->getDirBuilder()->toNamespaceName($this->getAlias()),
//'module' => $this->getDirBuilder()->toNamespaceName($this->getModule()),
            'alias' => $this->getAlias(),
            'entity' => $this->getEntity(),
            'translation_prefix' => $this->getTranslationPrefix(),
            'module' => $this->getModule(),
            'options' => $this->getOptions(),
            'entity_analyzer' => $this->getEntityAnalyzer(),
            'entity_class' => $this->getEntityClass(),
            'entity_namespace' => $this->getNamespace($this->getEntityClass()),
            'bundle' => $this->getDirBuilder()->getBundle(),
            'bundle_short' => str_replace('\\', '', $this->getDirBuilder()->getBundle()),
            'name' => $name,
            'entity_singularized' => $this->getEntitySingularized(),
            'entity_pluralized' => $this->getEntityPluralized(),
            'locale' => $this->getOptions()['locale'],
            'entity_analyzer_factory' => $this->entityAnalyzerFactory,
            'view' => $name
        ));
        return $filePath;
    }

    public function addServiceToExtensionClass()
    {

        $bundle = $this->getDirBuilder()->getBundle();
        $class = str_replace(['\\', '/'], '', $bundle);
        $dependencyInjectionClass = sprintf('%s\DependencyInjection\%sExtension', $bundle, substr($class, 0, -6));
        $extensionManipulator = new ExtensionManipulator($dependencyInjectionClass, $this->getModule(), $this->snakeCase($this->getDirBuilder()->toNamespaceName($this->getAlias())));
        $extensionManipulator->addService();
    }

    public function generateGetView()
    {

        return $this->renderView('get');
    }

    public function generateListView()
    {

        return $this->renderView('list');
    }

    public function generateDeleteView()
    {

        return $this->renderView('delete');
    }

    public function generateIndexView()
    {

        return $this->renderView('index');
    }

    public function generateInsertView()
    {

        return $this->renderView('new');
    }

    public function generateFilterView()
    {

        return $this->renderView('filter');
    }

    public function generateEditView()
    {

        return $this->renderView('edit');
    }

    public function generateTabsView()
    {

        return $this->renderView('tabs');
    }

    public function generateCustomizedModel()
    {

        return $this->renderModel('Model');
    }

    public function generateCustomizedRepository()
    {

        return $this->renderRepository('Repository');
    }

    public function generateTypeaheadModel()
    {
        return $this->renderModel('Typeahead');
    }

    public function generateCustomizedPaginatorModel()
    {

        return $this->renderModel('PaginatorModel');
    }

    public function generateModelTests()
    {

        return $this->renderTest('ModelTest');
    }

    public function generateSmokeTests()
    {

        return $this->renderTest('SmokeTest');
    }

    public function generateFormType($prefix)
    {

        return $this->renderForm(sprintf('%s%s', $prefix, 'FormType'));
    }

    public function generateFilterFormType()
    {

        return $this->renderForm('FilterFormType');
    }

    public function addTranslations()
    {

        var_dump('adding Translations');
    }

    protected function getEntityMapper()
    {
        return $this->entityMapper;
    }

    protected function getDirBuilder()
    {
        return $this->dirBuilder;
    }

    protected function getAlias()
    {
        return $this->alias;
    }

    protected function getModule()
    {
        return $this->module;
    }

    protected function getOptions()
    {
        return $this->options;
    }

    protected function getEntityAnalyzer()
    {
        return $this->entityAnalyzer;
    }

    protected function getEntityClass()
    {
        return $this->entityClass;
    }
    
    
    protected function getChecksumControllerDirBuidler()
    {
        return $this->checksumControllerDirBuidler;
    }


    protected function getEntity()
    {

        $entityClassArr = explode('\\', $this->getEntityClass());
        return end($entityClassArr);
    }

    protected function renderFile($template, $target, $parameters)
    {
        self::mkdir(dirname($target));

        return $this->dumpFile($target, $this->render($template, $parameters));
    }

    public function dumpFile($filename, $content)
    {

        if (file_exists($filename)) {
            
            if ($this->checksumController->checkChecksum($filename,$content)) {
                
                $this->checksumController->addFile($filename);
                
            } else {
               
                $folderPath = $this->getChecksumControllerDirBuidler()->getTempFolderPath($filename);
                
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
                
                return file_put_contents($this->getChecksumControllerDirBuidler()->getTempFilePath($filename), $content);
            }
        } else {
            $this->checksumController->addFile($filename);
            
        }

        return file_put_contents($filename, $content);
    }

}
