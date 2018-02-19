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
    public function getDqlTransformer($tableAlias, $field)
    {
        $options = $this->getOptions();
        $mode = "GROUP_CONCAT(#col SEPARATOR ',')";
        if(isset($options['mode'])) {
            $mode = $options['mode'];
        }
        $dql = str_replace('#col', $tableAlias . '.' . $field, $mode);
        return $dql;
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