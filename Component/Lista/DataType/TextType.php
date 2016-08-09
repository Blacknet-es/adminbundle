<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class TextType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'text';
    }

    public function configureOptions()
    {
        return array (
            'max_length' => 255
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $options = array_merge($this->configureOptions(), $options);
        if($options['max_length'] && $options['max_length'] < strlen($value)) {
            return substr($value, 0, $options['max_length'] - 3) . '...';
        }
        return $value;
    }
}