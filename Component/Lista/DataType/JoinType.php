<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class JoinType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'join';
    }

    public function configureOptions()
    {
        return array (
            'data_transformer' => null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        if (isset($options['data_transformer'])) {
            $transformer = new $options['data_transformer']($this->getKey(), $this->getOptions());
            return $transformer->getDataTransformer($value, $options);
        }
        return $value;
    }
}