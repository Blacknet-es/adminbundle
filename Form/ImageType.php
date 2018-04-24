<?php

namespace Trendix\AdminBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    protected $isGallery = false;
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
            ->add('image', HiddenType::class)
            ->add('originalImage', HiddenType::class);

        //$builder->get('image')->addModelTransformer(new ImageTransformer($this->manager));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $uploadConfig = array(
            'uploadUrl' => __DIR__.'/../../../../web/uploads/images/',       // required - see explanation below (you can also put just a dir path)
            'webDir' => '/uploads/images/',              // required - see explanation below (you can also put just a dir path)
            'fileExt' => '*.jpg;*.png;*.jpeg',    //optional
        );
        $cropConfig = array(
            'minWidth' => 32,
            'minHeight' => 32,
            'aspectRatio' => false,              //optional
            'thumbs' => array(                  //optional
                array(
                    'maxWidth' => 150,
                    'maxHeight' => 150,
                    'useAsFieldImage' => true  //optional
                )
            )
        );

        $resolver->setDefaults(array(
            'data_class' => 'Trendix\AdminBundle\Entity\Image',
            'cropConfig' => $cropConfig,
            'uploadConfig' => $uploadConfig
        ));

        $isGallery = $this->isGallery;
        $resolver->setNormalizer('uploadConfig', function(Options $options, $value) use ($uploadConfig, $isGallery) {
            $config = array_merge($uploadConfig, $value);

            if($isGallery){
                $config['uploadUrl'] = $config['uploadUrl'].'gallery/';
                $config['webDir'] = $config['webDir'].'gallery/';
            }
            return $config;
        });
        $resolver->setNormalizer('cropConfig', function(Options $options, $value) use ($cropConfig) {
            return array_merge($cropConfig, $value);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $originalPhotoFieldId = null;

        $uploadConfig = $options['uploadConfig'];
        $cropConfig = $options['cropConfig'];

        $fieldImage = null;
        if(isset($cropConfig['thumbs']) && count($thumbs = $cropConfig['thumbs']) > 0)
        {
            foreach ($thumbs as $thumb) {
                if(isset($thumb['useAsFieldImage']) && $thumb['useAsFieldImage'])
                {
                    $fieldImage = $thumb;
                }
            }
        }

        $view->vars['options'] = array('uploadConfig' => $uploadConfig, 'cropConfig' => $cropConfig, 'fieldImage' => $fieldImage);
        $view->vars['attr'] = array('style' => 'opacity: 0;width: 0; max-width: 0; height: 0; max-height: 0;');
    }

    public function getName()
    {
        return 'trendix_image';
    }
}
