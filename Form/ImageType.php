<?php

namespace Trendix\AdminBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', 'hidden', $options);

        $builder->get('image')->addModelTransformer(new ImageTransformer($this->manager));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trendix\AdminBundle\Entity\Image',
            'cropConfig' => array(
                'uploadUrl' => $this->get('kernel')->getRootDir() . '/../web/uploads/images/',       // required - see explanation below (you can also put just a dir path)
                'webDir' => '/uploads/images/',              // required - see explanation below (you can also put just a dir path)
                'fileExt' => '*.jpg;*.png;*.jpeg',    //optional
            ),
            'uploadConfig' => array(
                'minWidth' => 1,
                'minHeight' => 1,
                'aspectRatio' => false,              //optional
                'thumbs' => array(                  //optional
                    array(
                        'maxWidth' => 180,
                        'maxHeight' => 400,
                        'useAsFieldImage' => true  //optional
                    )
                )
            )
        ));
    }

    public function getName()
    {
        return 'trendix_image';
    }
}
