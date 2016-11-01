<?php


namespace Trendix\AdminBundle\Component\Lista;


use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ListaAbstractType implements ListaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        return $this;
    }

    /**
     * Configure filters type
     *
     * @param $filters ListaFilterInterface builder
     * @return ListaTypeInterface
     */
    public function buildFilters(ListaFilterInterface $filters)
    {
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return array('downloadable' => false);
    }
}