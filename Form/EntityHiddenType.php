<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 6/11/18
 * Time: 10:19
 */

namespace Trendix\AdminBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trendix\AdminBundle\Form\DataTransformer\HiddenEntityTransformer;

/**
 * Entity hidden custom type class definition
 */
class EntityHiddenType extends AbstractType
{
    /**
     * @var DataTransformerInterface $transformer
     */
    private $transformer;

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats
        //$builder->addModelTransformer(new HiddenEntityTransformer($options['em'], $options['class']));
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'entityhidden';
    }
}