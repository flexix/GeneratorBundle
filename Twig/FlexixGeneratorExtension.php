<?php

namespace Flexix\GeneratorBundle\Twig;
use Doctrine\Common\Inflector\Inflector;

class FlexixGeneratorExtension extends \Twig_Extension
{
    public function getFilters()
    {
  
        return array(
          
             new \Twig_SimpleFilter('snake',[$this, 'snakeFilter']),
             new \Twig_SimpleFilter('dash',[$this, 'dashFilter']),
             new \Twig_SimpleFilter('ucfirst',[$this, 'ucfirstFilter']),
             new \Twig_SimpleFilter('lcfirst',[$this, 'lcfirstFilter']),
             new \Twig_SimpleFilter('pluralize',[$this, 'pluralizeFilter']),
             new \Twig_SimpleFilter('nextLetter',[$this, 'nextLetter'])
        );
    }

    public function snakeFilter($text)
    {
        
        $text=str_replace('-','_',$text);
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $text)),'_');
    }
    
      public function dashFilter($text)
    {
        $text=str_replace('_','-',$text);
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $text)),'-');
    }
    
    
    public function ucfirstFilter($text)
    {
        
        return ucfirst($text); 
    }
    
    
    public function lcfirstFilter($text)
    {
        
        return lcfirst($text);
    }
    
    public function pluralizeFilter($text)
    {
        
        return Inflector::pluralize($text);
    }
    
    public function getName()
    {
        return 'flexix_generator_extension';
    }
    
    
    public function nextLetter($letter)
    {
        return ++$letter;
    }
    
}