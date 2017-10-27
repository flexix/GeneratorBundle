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

/**
 * Generates a CRUD controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DirBuilder extends Generator
{

    protected $alias;
    protected $module;
    protected $rootDir;
    protected $srcDir;
    protected $viewDir;
    protected $formDir;
    protected $testDir;
    protected $serviceDir;
    protected $modelDir;
    protected $repositoryDir;
    protected $bundleDir;
    protected $bundle;
    protected $entityClass;
    protected $translationsDir;
    protected $dependencyInjectionPath;
    protected $projectDir;

    public function __construct($rootDir, $alias, $module, $entityClass)
    {

        $this->rootDir = $rootDir;
        $this->alias = $alias;
        $this->module = $module;
        $this->entityClass = $entityClass;
    }

   
    public function getSrcDir()
    {
        if (!$this->srcDir) {
            $this->srcDir = str_replace('\app', '\src', $this->rootDir);
        }
        return $this->srcDir;
    }

    public function getBundleDir()
    {
        if (!$this->bundleDir) {

            $this->bundleDir = sprintf('%s%s%s', $this->getSrcDir(), DIRECTORY_SEPARATOR, $this->getBundle());
        }
        return $this->bundleDir;
    }

    public function getBundle()
    {
        if (!$this->bundle) {

            $arr = explode("\Entity\\", $this->entityClass);
            $this->bundle = $arr[0];
        }
        return $this->bundle;
    }

    public function toNamespaceName($text)
    {
        $textArr = explode('-', $text);
        $resultArr = [];

        foreach ($textArr as $fragment) {
            $resultArr[] = ucfirst($fragment);
        }
        return implode('', $resultArr);
    }

    public function toSnaceCaseName($text)
    {
        $textArr = explode('-', $text);
        $resultArr = [];

        foreach ($textArr as $fragment) {
            $resultArr[] = lcfirst($fragment);
        }
        return implode('_', $resultArr);
    }

    protected function createDir($dir)
    {
        if (!file_exists($dir)) {
            self::mkdir($dir);
        }
        return $dir;
    }

    protected function getFolderName($ucfirst = false)
    {

        if ($ucfirst) {
            $module = ucfirst($this->module);
            $alias = ucfirst($this->alias);
        } else {
            $module = $this->module;
            $alias = $this->alias;
        }

        return sprintf('%s%s%s', $this->toNamespaceName($module), DIRECTORY_SEPARATOR, $this->toNamespaceName($alias));
    }

    public function getViewDir()
    {

        if (!$this->viewDir) {
            $viewDir = sprintf('%s/Resources/views/%s', $this->rootDir, $this->getFolderName());
            $this->viewDir = $this->createDir($viewDir);
        }
        return $this->viewDir;
    }

    public function getFormDir()
    {

        if (!$this->formDir) {
            $formDir = sprintf('%s/Form/Type/%s', $this->getBundleDir(), $this->getFolderName(true));
            $this->formDir = $this->createDir($formDir);
        }
        return $this->formDir;
    }

    public function getServiceDir()
    {

        if (!$this->serviceDir) {
            $serviceDir = sprintf('%s/Resources/config/services/%s', $this->getBundleDir(), $this->getModule());
            $this->serviceDir = $this->createDir($serviceDir);
        }
        return $this->serviceDir;
    }

    public function getTranslationsDir()
    {

        if (!$this->translationsDir) {
            $translationsDir = sprintf('%s/Resources/translations/', $this->getBundleDir());
            $this->translationsDir = $this->createDir($translationsDir);
        }
        return $this->translationsDir;
    }

    public function getModelDir()
    {

        if (!$this->modelDir) {
            $modelDir = sprintf('%s/Model/%s', $this->getBundleDir(), $this->getFolderName(true));
            $this->modelDir = $this->createDir($modelDir);
        }
        return $this->modelDir;
    }

    public function getRepositoryDir()
    {

        if (!$this->repositoryDir) {
            $repositoryDir = sprintf('%s/Repository/%s', $this->getBundleDir(), $this->getFolderName(true));
            $this->repositoryDir = $this->createDir($repositoryDir);
        }
        return $this->repositoryDir;
    }

    public function getTestDir()
    {

        if (!$this->testDir) {
            $testDir = sprintf('%s/Tests/Functional/%s', $this->getBundleDir(), $this->getFolderName(true));
            $this->testDir = $this->createDir($testDir);
        }
        return $this->testDir;
    }

    protected function getModule()
    {
        return $this->module;
    }


}
