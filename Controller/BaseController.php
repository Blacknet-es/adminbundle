<?php

namespace Trendix\AdminBundle\Controller;

use Trendix\AdminBundle\Component\Lista\ListaAbstractType;
use Trendix\AdminBundle\Utility\Menu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    private $data = array();
    public $em;
    public $user;

    public function __construct()
    {
    }

    /**
     * Inicializa Doctrine
     */
    public function initializeEntityManager()
    {
        $this->em = $this->get('doctrine')->getManager();
    }

    /**
     * Añade un elemento a los datos
     * @param $key
     * @param $value
     * @return $this
     */
    public function addData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $name => $dato) {
                $this->addData($name, $dato);
            }
        } else {
            $this->data[$key] = $value;
            return $this;
        }

    }

    /**
     * Obtiene los datos que devolverá a la vista
     * @return array
     */
    public function getData()
    {
        return array_merge($this->getAppData(), $this->data);
    }

    /**
     * Genera las funciones necesarias para las listas
     * @param ListaAbstractType $lista
     * @param array $options
     * @return array
     */
    public function createList(ListaAbstractType $lista, $options = array())
    {
        $list = $this->get('admin.lista.factory');
        return $list->create($lista, $options);
    }

    /**
     * Datos generales de la aplicación que siempre debe devolver
     */
    private function getAppData()
    {
        $menu = new Menu($this->get('service_container'));
        return array(
            'app_name' => $this->getParameter('app_name'),
            'principalMenu' => $menu->getArrayMenu()
        );
    }
}
