<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

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
        if($value) {
            $options = array_merge($this->configureOptions(), $options);
            if($value instanceof \DateTime) {
                return $value->format($options['format']);               
            } else {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                return $date->format($options['format']);
            }
        } else {
            return '';
        }
    }
}