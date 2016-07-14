<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PercentType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'percent';
    }

    public function configureOptions()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        return $value * 100 . ' %';
    }
}