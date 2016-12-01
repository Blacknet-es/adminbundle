<?php


namespace Trendix\AdminBundle\Component\Sitemap;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class used for managing url entities
 */
class SitemapEntityCollection
{
    /**
     * Entity Name with namespace
     * @var string
     */
    private $entity;

    /**
     * Repository function to exec
     * @var string
     */
    private $function;

    /**
     * Repository Criteria filter
     * @var mixed
     */
    private $criteria;

    /**
     * Path url
     * @var mixed
     */
    private $path;

    /**
     * Path params
     * @var mixed
     */
    private $path_params;

    /**
     * @var \DateTime
     */
    private $last_mod;

    /**
     * @var string
     */
    private $change_freq;

    /**
     * @var string
     */
    private $priority;


    public function __construct($options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);

        $this->entity = $this->options['entity'];
        $this->function = $this->options['function'];
        $this->criteria = $this->options['criteria'];
        $this->path = $this->options['path'];
        $this->path_params = $this->options['path_params'];
        $this->last_mod = $this->options['last_mod'];
        $this->change_freq = $this->options['change_freq'];
        $this->priority = $this->options['priority'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => '',
            'function' => 'findAll',
            'criteria' => null,
            'path' => '',
            'path_params' => array('id', 'getId'),
            'slug_param' => '',
            'last_mod' => new \DateTime(),
            'change_freq' => SitemapUrl::CHANGEFREQ_DAILY,
            'priority' => '1'

        ));

        $resolver->setRequired('entity');
        $resolver->setRequired('path');
        $resolver->setRequired('path_params');
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return SitemapEntityCollection
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function
     * @return SitemapEntityCollection
     */
    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param mixed $criteria
     * @return SitemapEntityCollection
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPathParams()
    {
        return $this->path_params;
    }

    /**
     * @param mixed $path_params
     * @return SitemapEntityCollection
     */
    public function setPathParams($path_params)
    {
        $this->path_params = $path_params;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return SitemapEntityCollection
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastMod()
    {
        return $this->last_mod;
    }

    /**
     * @param \DateTime $last_mod
     * @return SitemapEntityCollection
     */
    public function setLastMod($last_mod)
    {
        $this->last_mod = $last_mod;
        return $this;
    }

    /**
     * @return string
     */
    public function getChangeFreq()
    {
        return $this->change_freq;
    }

    /**
     * @param string $change_freq
     * @return SitemapEntityCollection
     */
    public function setChangeFreq($change_freq)
    {
        $this->change_freq = $change_freq;
        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     * @return SitemapEntityCollection
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }
}