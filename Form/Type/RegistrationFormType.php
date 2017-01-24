<?php

namespace Trendix\AdminBundle\Form\Type;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Trendix\AdminBundle\Form\ImageType;

class RegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct()
    {
        $this->class = 'Trendix\AdminBundle\Entity\User';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->remove('username')
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('photo', ImageType::class, array(
                'required'      => true,
                'label' => 'Foto de perfil',
                'uploadConfig' => array(
                    'uploadUrl' => __DIR__.'/../../../../../web/uploads/images/',
                    'webDir' => '/uploads/images/',
                    'fileExt' => '*.jpg;*.png;*.jpeg',    //optional
                ),
                'cropConfig' => array(
                    'minWidth' => 128,
                    'minHeight' => 128,
                    'aspectRatio' => true,
                    'thumbs' => array(
                        array(
                            'maxWidth' => 128,
                            'maxHeight' => 128
                        )
                    )
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'csrf_token_id' => 'registration',
            // BC for SF < 2.8
            'intention'  => 'registration',
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }
}
