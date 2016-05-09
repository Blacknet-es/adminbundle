<?php

namespace Trendix\AdminBundle\Component\Lista;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface DataTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataTransformer($value, $options);

    /**
     * {@inheritdoc}
     */
    public function getName();

    /**
     * {@inheritdoc}
     */
    public function configureOptions();
}