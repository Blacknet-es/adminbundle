<?php


namespace Trendix\AdminBundle\Component\Sitemap;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class used for generate sitemap XML
 */
class SitemapGenerator
{
    const SITEMAP_TEMPLATE = "TrendixAdminBundle:Sitemap:sitemap.xml.twig";

    /**
     * @var ArrayCollection
     */
    private $sitemap;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templating;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * set de final url collection to send view
     * @var ArrayCollection
     */
    private $finalCollection;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');
        $this->templating = $this->container->get('templating');
        $this->router = $this->container->get('router');
        $this->template = self::SITEMAP_TEMPLATE;
        $this->finalCollection = new ArrayCollection();
    }

    /**
     * @param $view
     * @return SitemapGenerator
     */
    public function setXmlTemplate($view)
    {
        $this->template = $view;
        return $this;
    }

    public function render(Sitemap $sitemap)
    {
        $this->sitemap = $sitemap->getSitemap();
        foreach ($this->sitemap as $element) {
            if ($element instanceof SitemapUrl) {
                $this->finalCollection->add($element);
            } elseif ($element instanceof SitemapEntityCollection) {
                $this->processEntityCollection($element);
            }
        }

        $response = $this->templating->renderResponse($this->template, array('urls' => $this->finalCollection));
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');
        return $response;
    }

    /**
     * @param SitemapEntityCollection $collection
     */
    private function processEntityCollection(SitemapEntityCollection $collection)
    {
        $repo = $this->em->getRepository($collection->getEntity());
        $result = $repo->{$collection->getFunction()}($collection->getCriteria());
        if (!empty($result)) {
            foreach ($result as $item) {
                $path = $this->getPath($item, $collection);
                $url = new SitemapUrl($path, $collection->getLastMod(), $collection->getChangeFreq(), $collection->getPriority());
                $this->finalCollection->add($url);
            }
        }
    }

    /**
     * @param SitemapEntityCollection $collection
     * @return string
     */
    private function getPath($item, SitemapEntityCollection $collection)
    {
        $path_params = $this->getPathParams($item, $collection);
        return $this->router->generate($collection->getPath(), $path_params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param SitemapEntityCollection $collection
     * @return array
     */
    private function getPathParams($item, SitemapEntityCollection $collection)
    {
        $path_params = $collection->getPathParams();
        $return_path_params = array();
        foreach ($path_params as $param => $method) {
            $return_path_params[$param] = $item->{$method}();
        }

        return $return_path_params;
    }
}