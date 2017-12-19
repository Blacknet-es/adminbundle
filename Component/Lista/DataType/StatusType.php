<?php

namespace Trendix\AdminBundle\Component\Lista\DataType;

use Trendix\AdminBundle\Component\Lista\DataAbstractType;

class StatusType extends DataAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'status';
    }

    public function configureOptions()
    {
        return array (
            'values' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        $result = '';
        if($value && isset($options['values'][$value])) {
            $options = array_merge($this->configureOptions(), $options);
            $icon = '';
            $style = '';
            if(isset($options['values'][$value]['icon'])) {
                $icon = $options['values'][$value]['icon'];
            }

            if(isset($options['values'][$value]['label'])) {
                $text = $options['values'][$value]['label'];
            } else {
                $text = $value;
            }

            if(isset($options['values'][$value]['color'])) {
                $style =  'color: ' . $options['values'][$value]['color'] . ';';
            }
            $result = '<div style="' . $style . '"><i class="' . $icon . '"></i> ' . $text . '</div>';
        }

        return $result;
    }
}