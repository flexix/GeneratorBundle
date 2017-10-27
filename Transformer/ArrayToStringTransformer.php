<?php
namespace Flexix\GeneratorBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of ArrayToJSONStringTransformer
 *
 * @author Mariusz
 */
class ArrayToStringTransformer implements DataTransformerInterface {
    //put your code here


    public function transform($array) {
        
        if(!is_array($array))
        {$array=[];}
        return implode("\n",$array);
    }

    public function reverseTransform($string)
    {
       $modelData = explode("\n",trim($string," "));
       if ($modelData == null) {
           throw new TransformationFailedException('String is not a valid JSON.');
       }

       return $modelData;
    }

}
