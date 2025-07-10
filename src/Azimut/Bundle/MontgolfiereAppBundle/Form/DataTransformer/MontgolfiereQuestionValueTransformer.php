<?php
/**
 * Created by mikaelp on 20-Sep-18 11:53 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\DataTransformer;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Symfony\Component\Form\DataTransformerInterface;

class MontgolfiereQuestionValueTransformer implements DataTransformerInterface
{

    /**
     * @var Question
     */
    protected $question;

    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    public function transform($dbValue)
    {
        if(is_null($dbValue)) {
            return null;
        }

        // @see reverseTransform
        $dbValue+= 10;

        $distribution = $this->question->getValuesDistribution();
        array_unshift($distribution, 0); // While 1000 is always the last element of the array, 0 is never present
        $value = $distribution[$dbValue];

        // Because we don't remember exactly where the user set its value, use the middle between this value and the one after
        $otherValue = count($distribution)-1 === $dbValue ? $distribution[$dbValue-1] : $distribution[$dbValue+1];

        return ($value + $otherValue) / 2;
    }

    public function reverseTransform($formValue)
    {
        if(is_null($formValue)) {
            return false;
        }

        foreach ($this->question->getValuesDistribution() as $key => $value) {
            if($formValue <= $value) {
                return $key-10;
            }
        }

        throw new \InvalidArgumentException;
    }
}
