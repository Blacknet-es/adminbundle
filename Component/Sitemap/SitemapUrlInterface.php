<?php

namespace Trendix\AdminBundle\Component\Sitemap;


interface SitemapUrlInterface
{

    /**
     * render element as xml
     * @return string
     */
    public function toXml();

    /**
     * list of used namespaces
     * @return array - [{ns} => {location}]
     */
    public function getCustomNamespaces();
}