<?php

namespace Trendix\AdminBundle\Form;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['lat_name'], $options['type'], array('label' => 'Latitud'))
            ->add($options['lng_name'], $options['type'], array('label' => 'Longitud'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trendix\AdminBundle\Entity\Location',
            'type'           => 'hidden',  // the types to render the lat and lng fields as
            'options'        => array(), // the options for both the fields
            'lat_options'  => array(),   // the options for just the lat field
            'lng_options' => array(),    // the options for just the lng field
            'lat_name'       => 'latitude',   // the name of the lat field
            'lng_name'       => 'longitude',   // the name of the lng field
            'error_bubbling' => false,
            'map_width'      => '100%',     // the width of the map
            'map_height'     => '400px',     // the height of the map
            'default_lat'    => 51.5,    // the starting position on the map
            'default_lng'    => -0.1245, // the starting position on the map
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['lat_name'] = $options['lat_name'];
        $view->vars['lng_name'] = $options['lng_name'];
        $view->vars['map_width'] = $options['map_width'];
        $view->vars['map_height'] = $options['map_height'];
        $view->vars['default_lat'] = $options['default_lat'];
        $view->vars['default_lng'] = $options['default_lng'];
        $view->vars['appendJavascript'] = $this->generateJavascripts($view->vars);
    }

    private function generateJavascripts($vars)
    {
        return $this->container->get('twig')->render('TrendixAdminBundle:forms:trendixLocationJavascript.html.twig', $vars);
    }

    public function getParent()
    {
        return FormType::class;
    }

    public function getBlockPrefix()
    {
        return 'trendix_google_maps';
    }
}
