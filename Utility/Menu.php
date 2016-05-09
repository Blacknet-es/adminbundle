<?php

namespace Trendix\AdminBundle\Utility;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;


class Menu
{
    protected $securityContext;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Translator
     */
    protected $trans;

    /**
     * @param Session
     */
    protected $session;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->trans = $this->container->get('translator');
        $this->session = new Session();
    }

    /**
     * Devuelve el prototipo de un elemento del menú
     * @return array
     */
    private function getPrototype()
    {
        return array(
            'id' => '',
            'label' => '',
            'icon' => null,
            'route' => null,
            'params' => array(),
            'cond' => true,
            'submenu' => array()
        );
    }

    public function getArrayMenu($menu = null)
    {
        if (null === $menu) {
            $menu = $this->getMenu();
        }

        $menuComplete = array();

        foreach ($menu as $option) {
            if (!isset($option['cond']) || $option['cond'] === true) {
                $optionComplete = $this->getPrototype();
                foreach ($option as $key => $value) {
                    if ($key != 'submenu') {
                        $optionComplete[$key] = $value;
                    } else {
                        $optionComplete[$key] = $this->getArrayMenu($option[$key]);
                    }

                }
                $menuComplete[] = $optionComplete;
            }
        }

        return $menuComplete;
    }

    private function getMenu()
    {
        return array(
            array( // Dashboard
                'id' => 'dashboard',
                'label' => $this->trans->trans('Dashboard'),
                'icon' => 'home',
                'route' => 'admin',
                'cond' => true
            ),
            array(
                'id' => 'hotel',
                'label' => $this->trans->trans('Hoteles'),
                'icon' => 'hotel',
                'submenu' => $this->getHotelesSubmenu()
            ),
            array(
                'id' => 'campos',
                'label' => $this->trans->trans('Campos'),
                'icon' => 'flag',
                'submenu' => $this->getCamposSubmenu()
            ),
            array(
                'id' => 'servicios',
                'label' => $this->trans->trans('Servicios'),
                'icon' => 'local-bar',
                'submenu' => $this->getServiciosSubmenu()
            ),

        );
    }

    private function getHotelesSubmenu()
    {
        return array(
            array(
                'label' => $this->trans->trans('Listado'),
                'route' => 'hotel_index',
            ),
            array(
                'label' => $this->trans->trans('Nuevo'),
                'route' => 'hotel_new',
            ),
        );
    }
    private function getCamposSubmenu()
    {
        return array(
            array(
                'label' => $this->trans->trans('Listado'),
                'route' => 'admin_campo_index',
            ),
            array(
                'label' => $this->trans->trans('Nuevo'),
                'route' => 'admin_campo_new',
            ),
        );
    }
    private function getServiciosSubmenu()
    {
        return array(
            array(
                'label' => $this->trans->trans('Listado'),
                'route' => 'admin_service_index',
            ),
            array(
                'label' => $this->trans->trans('Nuevo'),
                'route' => 'admin_service_new',
            ),
        );
    }

}