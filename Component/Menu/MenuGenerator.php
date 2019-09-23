<?php

namespace Trendix\AdminBundle\Component\Menu;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Trendix\AdminBundle\Utility\Menu;

class MenuGenerator
{
    private $container;

    private $params;

    public function __construct(Container $container, ParameterBagInterface $params)
    {
        $this->container = $container;
        $this->params = $params;
    }

    public function generateMenu()
    {
        $menuClass = $this->params->get('menu_class');
        if(!$menuClass) {
            $menuClass = Menu::class;
        }
        return new $menuClass($this->container);
    }
}