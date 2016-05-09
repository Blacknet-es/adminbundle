<?php

namespace Trendix\AdminBundle\Component\Lista;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ListaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildList(ListaAbstractBuilder $builder, array $options);

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function buildFilters(ListaFilterInterface $filters);

    /**
     * {@inheritdoc}
     */
    public function getClass();

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver);
}