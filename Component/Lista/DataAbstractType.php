<?php

namespace Trendix\AdminBundle\Component\Lista;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class DataAbstractType implements DataTypeInterface
{
    protected $key;
    protected $options;

    public function __construct($key, $options)
    {
        $this->key = $key;
        $this->options = $options;
    }

    public function getName()
    {
        return '';
    }

    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options = array())
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions()
    {
        return array();
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return DataAbstractType
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     * @return DataAbstractType
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /** Returns if the current field has the "hidden" option set to true
     * @return bool
     */
    public function isHidden()
    {
        return isset($this->getOptions()['hidden']) && $this->getOptions()['hidden'];
    }

    /**
     * Get the label, title or header of filed list
     * @return string
     */
    public function getLabel()
    {
        if (isset($this->options['label'])) {
            return $this->options['label'];
        }

        return ucfirst($this->key);
    }
}