<?php


namespace Trendix\AdminBundle\Lista;


use AdminBundle\Component\Lista\ListaAbstractBuilder;
use AdminBundle\Component\Lista\ListaAbstractType;

class UserLista extends ListaAbstractType
{
    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('username', 'text', array('label' => 'Nombre de Usuario'))
            ->add('createdAt', 'date', array('label' => 'Fecha de Creación'))
        ;
        return $this;
    }

    public function getClass()
    {
        return 'Trendix\UserBundle\Entity\User';
    }
}