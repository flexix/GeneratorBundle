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
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Inflector\Inflector;

class TranslationsBuilder {

    protected $data;
    protected $path;
    protected $dir;
    protected $lang;
    protected $module;
    protected $alias;
    protected $fields;
    protected $entity;
    protected $defaultLabels = [
        '_filter' => 'Filter',
        '_list' => 'List',
        '_get' => 'View',
        '_edit' => 'Edit',
        '_new' => 'New',
        '_add' => 'Add',
        
    ];

    const REPLACE_MASK = '/[A-Z]([A-Z](?![a-z]))*/';
    const FILENAME = 'messages';

    public function __construct($dir, $lang, $module, $alias, $fields,$entity) {

        $this->dir = $dir;
        $this->lang = $lang;
        $this->path = sprintf('%s%s%s.%s.yml', $dir, DIRECTORY_SEPARATOR, self::FILENAME, $this->lang);
        $this->data = $this->getData($dir, $this->path);
        $this->module = $module;
        $this->alias = $alias;
        $this->fields = $fields;
        $this->entity = $entity;
    }

    protected function getData($dir, $path) {

        $data = null;
        if (file_exists($path)) {
            $data = Yaml::parse(file_get_contents($path));
        }



        if (!$data) {
            $data = [];
        }
        return $data;
    }

    protected function getSnakeCase($text) {
        return str_replace('-', '_', ltrim(strtolower(preg_replace(self::REPLACE_MASK, '_$0', $text)), '_'));
    }

    protected function  addDefaultLabels()
    {
         foreach ($this->defaultLabels as $labelName => $value)
        {
            $pathArr = [$this->getSnakeCase($this->module), $this->getSnakeCase($this->alias), $labelName];
            $this->setValue($this->data, $pathArr, $value);
            
        }
        
    }
    
    protected function  addEntityPluralized()
    {
        
            $entityArr=preg_split('/(?=[A-Z])/',$this->entity);
            
            for($i=0; $i<count($entityArr);$i++)
            {
                $entityArr[$i]=ucfirst($entityArr[$i]);
            }
            
            $labelName=$this->getSnakeCase(lcfirst(Inflector::pluralize($this->entity)));
            $pathArr = [$this->getSnakeCase($this->module), $this->getSnakeCase($this->alias), $labelName];
            $this->setValue($this->data, $pathArr, trim(ucfirst(implode(' ',$entityArr))));
        
    }
    
    protected function  addEntity()
    {
        
            $entityArr=preg_split('/(?=[A-Z])/',$this->entity);
            
            for($i=0; $i<count($entityArr);$i++)
            {
                $entityArr[$i]=ucfirst($entityArr[$i]);
            }
            
            $labelName=$this->getSnakeCase(lcfirst($this->entity));
            $pathArr = [$this->getSnakeCase($this->module), $this->getSnakeCase($this->alias), $labelName];
            $this->setValue($this->data, $pathArr, trim(ucfirst(implode(' ',$entityArr))));
        
    }
    
    
    public function addTranslations() {


        $this->addDefaultLabels();
        $this->addEntityPluralized();
        $this->addEntity();
        
        foreach ($this->fields as $fieldName => $field) {

            $fieldName = $this->getSnakeCase($fieldName);
            $pathArr = [$this->getSnakeCase($this->module), $this->getSnakeCase($this->alias), $fieldName];
            $this->setValue($this->data, $pathArr, $fieldName);

            if (in_array($field['type'], ['datetime', 'datetime_immutable', 'datetimetz', 'datetimetz_immutable', 'date', 'date_immutable'])) {

                $dateMin = sprintf('%s_%s', $fieldName, 'date_min');
                $dateMinArr = [$this->module, $this->alias, $dateMin];
                $this->setValue($this->data, $dateMinArr, $dateMin);

                $dateMax = sprintf('%s_%s', $fieldName, 'date_max');
                $dateMaxArr = [$this->module, $this->alias, $dateMax];
                $this->setValue($this->data, $dateMaxArr, $dateMax);

                $dateTimeMin = sprintf('%s_%s', $fieldName, 'date_time_min');
                $dateTimeMinArr = [$this->module, $this->alias, $dateTimeMin];
                $this->setValue($this->data, $dateTimeMinArr, $dateTimeMin);

                $dateTimeMax = sprintf('%s_%s', $fieldName, 'date_time_max');
                $dateTimeMaxArr = [$this->module, $this->alias, $dateTimeMax];
                $this->setValue($this->data, $dateTimeMaxArr, $dateTimeMax);
            }
        }

        $this->save();
    }

    public function save() {

        file_put_contents($this->path, $this->dump());
        chmod($this->path, 0664);
    }

    public function dump() {

        return Yaml::dump($this->data, 4);
    }

    function setValue(&$data, $path, $value) {

        $temp = &$data;

        foreach ($path as $key) {
            $temp = &$temp[$key];
        }

        if (!$temp) {
            $temp = $value;
        }

        return $value;
    }

}
