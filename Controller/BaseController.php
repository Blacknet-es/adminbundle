<?php

namespace Trendix\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Trendix\AdminBundle\Component\Lista\ListaAbstractBuilder;
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
    protected function getAppData()
    {
        $menu = $this->getMenu();
        return array(
            'app_name' => $this->getParameter('app_name'),
            'principalMenu' => $menu->getArrayMenu()
        );
    }

    /**
     * @return Menu Returns the user chosen menu class instance.
     */
    protected function getMenu()
    {
        $menuClass = $this->getParameter('menu_class');
        if(!$menuClass) {
            $menuClass = 'Trendix\AdminBundle\Utility\Menu';
        }
        return new $menuClass($this->get('service_container'));
    }

    /**
     * @return User Current User object
     */
    public function getCurrentUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }

    /**
     * @return object Security Context Service
     */
    public function userIsLogged()
    {
        return $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     *
     * Generate List View
     *
     * @param ListaAbstractType $lista
     * @param $title
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateListWithRenderView(ListaAbstractType $lista, $title)
    {
        $lista = $this->createList($lista);
        $request = Request::createFromGlobals();

        $this->addData('lista', $lista->createView());
        $this->addData('title', $title);

        if ($request->isMethod('POST')) {
            return $this->render('TrendixAdminBundle:Lista:lista.html.twig', $this->getData());
        } else {
            return $this->render('TrendixAdminBundle:Lista:index.html.twig', $this->getData());
        }
    }


    /**
     * Delete element if exists and return JSON response
     * @param $object
     * @param $id
     * @return JsonResponse
     */
    public function deleteElementWithJsonResponse($object, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository($object)->find($id);
        $em->remove($service);
        $em->flush();

        $data = array(
            'type' => 'OK',
            'reload' => true
        );

        return new JsonResponse($data);
    }
}
