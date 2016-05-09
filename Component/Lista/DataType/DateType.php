<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'date';
    }

    public function configureOptions()
    {
        return array (
            'format' => 'd-m-Y H:i:s'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $options = array_merge($this->configureOptions(), $options);
        return $value->format($options['format']);
    }
}