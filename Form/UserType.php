<?php

namespace Trendix\AdminBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    private $holderOrTeacher;
    /**
     * @param bool|false $holderOrTeacher Must be true if the form must not ask for a holder user
     */
    function __construct($holderOrTeacher = false)
    {
        $this->holderOrTeacher = $holderOrTeacher;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createdAt', 'date', array(
                'label' => 'forms.created_at',
                'widget' => 'single_text',
                'attr' => array(
                    'class' => 'input-mask datepicker',
                    'data-mask' => '00/00/0000'
                ),
                'placeholder' => '24/09/2015',
                'format' => 'dd/MM/yyyy',
                'html5' => false
            ))
            ->add('name', 'text', array(
                'label' => 'forms.name'
            ))
            ->add('lastName', 'text', array(
                'label' => 'forms.last_name'
            ))
            ->add('email', 'email', array(
                'label' => 'forms.email'
            ))
            ->add('phone', 'text', array(
                'label' => 'forms.phone'
            ))
            ->add('mobilePhone', 'text', array(
                'label' => 'forms.mobile_phone'
            ))
        ;

        $builder->add('save', 'submit', array('label' => 'forms.save'));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trendix\UserBundle\Entity\User'
        ));
    }
}
