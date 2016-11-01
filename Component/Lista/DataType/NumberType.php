<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'number';
    }

    public function configureOptions()
    {
        return array (
            'editable' => true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $options = array_merge($this->configureOptions(), $options);
        $result = $value;
        if($options['editable']) {
            $result = '<a href="#" class="update-value" data-value="' . ($value-1) . '">
            <div class="zmdi zmdi-minus-square"></div></a> ' . $value .
            ' <a href="#" class="update-value" data-value="' . ($value+1) . '">
            <div class="zmdi zmdi-plus-square"></div></a>';
        }
        return $result;
    }
}