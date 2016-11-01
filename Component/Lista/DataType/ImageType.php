<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class ImageType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image';
    }

    public function configureOptions()
    {
        return array (
            'alt' => $this->getLabel(),
            'width' => '',
            'height' => '',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $dir = '';
        if(isset($options['dir'])) {
            $dir = $options['dir'];
            unset($options['dir']);
        }
        unset($options['label']);
        $options = $this->handleOptions($options);

        if($value) {
            $value = $dir . $value;
            return '<img src="' .$value . '" ' . $options . ' >';
        } else {
            //Podría ser un placeholder
            return '';
        }
    }

    private function handleOptions($options = array())
    {
        $defaultOptions = $this->configureOptions();
        $mergedOptions = array_merge($defaultOptions, $options);
        $processedOptions = '';

        foreach ($mergedOptions as $key => $value) {
            if ($value != '') {
                $processedOptions = ' '.$key.'="'.$value.'" ';
            }
        }

        return $processedOptions;
    }
}