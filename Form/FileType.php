<?php

namespace Trendix\AdminBundle\Form;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Trendix\AdminBundle\Entity\TrendixFile;


class FileType extends AbstractType
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path', HiddenType::class)
            ->add('fileupload', \Symfony\Component\Form\Extension\Core\Type\FileType::class,
                array(
                    'attr' => array('class' => 'fileupload'),
                    'mapped' => false
                )
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['path'] = '/uploads/files';
        $view->vars['appendJavascript'] = $this->generateJavascripts($view->vars);
    }

    private function generateJavascripts($vars)
    {
        return $this->container->get('twig')->render('TrendixAdminBundle:forms:trendixFileJavascript.html.twig', $vars);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => TrendixFile::class,
            'type' => 'hidden',  // the types to render the lat and lng fields as
            'options' => array(), // the options for both the fields
        ));
    }

    public function getBlockPrefix()
    {
        return 'trendix_file';
    }

    public function getParent()
    {
        return FormType::class;
    }

}