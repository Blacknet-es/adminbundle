<?php


namespace Trendix\AdminBundle\Lista;


use Trendix\AdminBundle\Component\Lista\ListaAbstractBuilder;
use Trendix\AdminBundle\Component\Lista\ListaAbstractType;

class NewsletterLista extends ListaAbstractType
{
    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('email', 'text', array('label' => 'Email'))
            ->addAction('primary', 'download', array('route' => 'admin_newsletter_export'), 'admin.export')
            ->addAction('delete', 'delete',
                array(
                    'route' => 'admin_subscriber_delete',
                    'route_list_params' => array('id' => 'id')
                )
            )
        ;
        return $this;
    }

    public function getClass()
    {
        return 'Trendix\AdminBundle\Entity\NewsletterSubscriber';
    }
}