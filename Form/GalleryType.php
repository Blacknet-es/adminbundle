<?php

namespace Trendix\AdminBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends ImageType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        parent::__construct($manager);
        $this->manager = $manager;
        $this->isGallery = true;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gallery', 'collection', array(
                'entry_type' => HiddenType::class,
                'allow_add' => true,
                'allow_delete' => true
            ));

        //$builder->get('image')->addModelTransformer(new ImageTransformer($this->manager));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'Trendix\AdminBundle\Entity\Gallery'
        ));
    }

    public function getName()
    {
        return 'trendix_gallery';
    }
}
