<?php
namespace Flexix\GeneratorBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of ArrayToJSONStringTransformer
 *
 * @author Mariusz
 */
class ArrayToJSONStringTransformer implements DataTransformerInterface {
    //put your code here

    /**
     * Transform an array to a JSON string
     */
    public function transform($array) {
        return json_encode($array);
    }

    public function reverseTransform($string)
    {
       $modelData = json_decode($string, true);
       if ($modelData == null) {
           throw new TransformationFailedException('String is not a valid JSON.');
       }

       return $modelData;
    }

}
