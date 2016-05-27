<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'boolean';
    }

    public function configureOptions()
    {
        return array (
            'editable' => true,
            'featured' => false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $options = array_merge($this->configureOptions(), $options);
        if($value) {
            if($options['featured']) {
                $class = 'zmdi-star enabled-yellow';
            } else {
                $class = 'zmdi-check enabled-green';
            }
        } else {
            if($options['featured']) {
                $class = 'zmdi-star-outline disabled-grey';
            } else {
                $class = 'zmdi-close disabled-red';
            }
        }
        $result = '<div class="zmdi ' . $class . '"></div>';
        if($options['editable']) {
            $val = $value ? 1 : 0;
            $result = '<a href="#" class="toggle-value" data-value="' . $val . '">' . $result . '</a>';
        }
        return $result;
    }
}