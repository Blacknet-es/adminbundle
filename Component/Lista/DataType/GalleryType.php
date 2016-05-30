<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class GalleryType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gallery';
    }

    public function getParent()
    {
        return JoinType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $arrayValue = unserialize($value);
        $arrayTransformed = array();

        foreach ($arrayValue as $value) {
            $img = new ImageType($value, $options);
            $arrayTransformed[$value] = $img->getDataTransformer($value, $options);
        }

        return $arrayTransformed;
    }
}