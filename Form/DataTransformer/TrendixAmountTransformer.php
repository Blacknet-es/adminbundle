<?php

namespace Trendix\AdminBundle\Form\DataTransformer;

use Trendix\AdminBundle\Utility\Numeric;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TrendixAmountTransformer implements DataTransformerInterface
{
    /**
     * Divisa
     *
     * @var Divisa
     */
    private $divisa;

    /**
     * Decimales
     *
     * @var integer
     */
    private $decimales;

    public function __construct($divisa = null, $decimales = null)
    {
        $this->divisa = $divisa;
        $this->decimales = $decimales;
    }

    /**
     * Transforms a float value to string with amount format.
     *
     * @param  float $float
     * @return String
     */
    public function reverseTransform($float)
    {
        $number = new Numeric($float, $this->divisa, $this->decimales);
        return $number->FloatToAmount();
    }

    /**
     * Transforms an amount formatted value to float.
     *
     * @param  String|null $string
     * @return float
     * @throws TransformationFailedException if error
     */
    public function transform($string)
    {

        if (null === $string) {
            return null;
        }
        return Numeric::createFromAmount($string, $this->divisa)->getValue();
    }
}