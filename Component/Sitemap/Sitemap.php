<?php


namespace Trendix\AdminBundle\Component\Sitemap;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class used for contains Sitemap
 */
class Sitemap
{
    /**
     * @var ArrayCollection
     */
    private $sitemap;

    public function __construct()
    {
        $this->sitemap = new ArrayCollection();
    }

    public function addElement($element)
    {
        $this->sitemap->add($element);
    }

    public function getSitemap()
    {
        return $this->sitemap;
    }
}