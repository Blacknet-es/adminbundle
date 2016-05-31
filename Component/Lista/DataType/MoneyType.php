<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class MoneyType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'money';
    }

    public function configureOptions()
    {
        return array (
            'currency' => '€',
            'after_amount' => true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $options = array_merge($this->configureOptions(), $options);
        if($options['after_amount']) {
            return $value . ' ' . $options['currency'];
        }
        return $options['currency'] . ' '. $value;
    }
}