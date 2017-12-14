<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class JoinManyType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'join_many';
    }

    public function getParent()
    {
        return JoinType::class;
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
        $arrayValue = $value;
        $arrayTransformed = array();

        // Fix error in app_dev
        if (empty($arrayValue)) {
            return $arrayTransformed;
        }

        foreach ($arrayValue as $key => $value) {
            $arrayTransformed[$key] = $value;
        }

        return $arrayTransformed;
    }
}