<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 30/11/18
 * Time: 17:21
 */

namespace Trendix\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
            'csrf_protection'    => true,
            'csrf_field_name'    => '_token',
            'attr' =>  ['data-widget' => 'text']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'confirm';
    }

    public function getParent()
    {
        return TextType::class;
    }

}