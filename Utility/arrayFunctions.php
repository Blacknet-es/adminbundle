<?php

namespace Trendix\AdminBundle\Utility;


class arrayFunctions {

    /* DataTypes values [string, numeric, TODO dates ]*/
    /* OrderTypes vallues [normal, inverse]*/
    public static function OrderMultiDim($array, $field, $options = array())
    {
        $options = self::setOrderMultiDimOptions($options);

        //Validaciones
        if (!is_array($array) || count($array) == 0) {
            return array();
        }

        if (empty($field)) {
            return $array;
        }

        //Obtenemos los valores del campo (field) especificado
        foreach ($array as $k => $v) {
            $b[$k] = strtolower($v[$field]);
        }

        if ($options['order_type'] == 'normal') {
            asort($b, self::getOrderFlag($options));
        } else {
            arsort($b, self::getOrderFlag($options));
        }


        foreach ($b as $key => $val) {
            $c[$key] = $array[$key];
        }

        return $c;
    }

    private static function setOrderMultiDimOptions($options) {
        $defaultOptions = array(
            'data_type' => 'string',
            'order_type' => 'normal'
        );

        foreach ($defaultOptions as $key => $value) {
            if (!isset($options[$key])) {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    private static function getOrderFlag($options) {
        $dataType = $options['data_type'];

        $dataTypes = array(
            'string' => SORT_NATURAL,
            'numeric' => SORT_NUMERIC,
            'dates' => SORT_REGULAR
        );

        return $dataTypes[$dataType];
    }

    public static function getUlFromStringArray($array){
        $s = '<ul>';
        foreach($array as $value){
            $s .= '<li>'.$value.'</li>';
        }
        $s .= '</ul>';

        return $s;
    }
}