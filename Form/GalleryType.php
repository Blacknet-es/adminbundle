<?php

namespace Trendix\AdminBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends AbstractType
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
            ->add('gallery', 'comur_gallery', $options);
        $builder->get('gallery')->addModelTransformer(new GalleryTransformer($this->manager));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trendix\AdminBundle\Entity\Gallery',
            'label' => false,
            'uploadConfig' => array(
                'uploadRoute' => 'comur_api_upload',        //optional
                'uploadUrl' => __DIR__.'/../../../../web/uploads/images',       // required - see explanation below (you can also put just a dir path)
                'webDir' => 'uploads/images/',              // required - see explanation below (you can also put just a dir path)
                'fileExt' => '*.jpg;*.gif;*.png;*.jpeg',    //optional
                'libraryDir' => false,                       //optional
                'libraryRoute' => 'comur_api_image_library', //optional
                'showLibrary' => false,                      //optional
                'saveOriginal' => 'originalImage',          //optional
                'generateFilename' => true          //optional
            ),
            'cropConfig' => array(
                'minWidth' => 1,
                'minHeight' => 1,
                'aspectRatio' => false,              //optional
                'cropRoute' => 'comur_api_crop',    //optional
                'forceResize' => false,             //optional
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
}
