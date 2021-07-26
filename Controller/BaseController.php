<?php

namespace Trendix\AdminBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Templating\EngineInterface;
use Trendix\AdminBundle\Component\Lista\ListaAbstractBuilder;
use Trendix\AdminBundle\Component\Lista\ListaAbstractType;
use Trendix\AdminBundle\Component\Lista\ListaFactory;
use Trendix\AdminBundle\Component\Lista\QueryList;
use Trendix\AdminBundle\Component\Menu\MenuGenerator;
use Trendix\AdminBundle\Entity\User;
use Trendix\AdminBundle\Utility\Menu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class BaseController extends AbstractController
{
    private $data = array();

    /**
     * @var EntityManagerInterface
     */
    public $em;

    /**
     * @var User
     */
    public $user;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
        $data = array(
            'app_name' => $this->getParameter('app_name'),
            'principalMenu' => $menu->getArrayMenu(),
            'hideTitle' => $this->hasParameter('hide_title') ? $this->getParameter('hide_title') : null,
            'titleLogo' => $this->hasParameter('title_logo') ? $this->getParameter('title_logo') : null,
            'hideMenu' => false,
            'registerButton' => $this->hasParameter('register_button') ? $this->getParameter('register_button') : null
        );

        if($this->hasParameter('custom_menu')) {
            $panel = $this->getParameter('custom_menu');
            if($panel) {
                $data['customMenu'] = $panel;
            }
        }

        if($this->hasParameter('locales')) {
            $locales = $this->getParameter('locales');
            if ($locales) {
                $data['locales'] = $locales;
            }
        }
        if($this->hasParameter('admin_message_module')) {
            $data['admin_message_module'] = $this->getParameter('admin_message_module');
            $data['admin_message_link'] = $this->getParameter('admin_message_link');
        }
        if($this->hasParameter('admin_primary_color')) {
            $data['admin_primary_color'] = $this->getParameter('admin_primary_color');
        }
        if($this->hasParameter('use_side_bar')) {
            $data['useSideBar'] = $this->getParameter('use_side_bar');
        }
        if($this->hasParameter('side_bar_template')) {
            $data['sideBarTemplate'] = $this->getParameter('side_bar_template');
        }
        if($this->hasParameter('content_class')) {
            $data['content_class'] = $this->getParameter('content_class');
        }
        if($this->hasParameter('translatable')) {
            $data['locales'] = $this->getParameter('locales');
        }
        if($this->hasParameter('admin_theme')) {
            $data['admin_theme'] = $this->getParameter('admin_theme');
        }
        if($this->hasParameter('copyright_text')) {
            $data['copyrightText'] = $this->getParameter('copyright_text');
        }
        if($this->hasParameter('base_template')) {
            $data['base_template'] = $this->getParameter('base_template');
        } else {
            $data['base_template'] = null;
        }

        return $data;
    }

    /**
     * @return Menu Returns the user chosen menu class instance.
     */
    protected function getMenu()
    {
        global $kernel;
        return $this->get('admin.menu.generator')->generateMenu();
    }

    /**
     * @deprecated Usar $this->getUser() en su lugar.
     * @return User Current User object
     */
    public function getCurrentUser()
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();
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

    /**
     * @param Request $request
     * @param $sql
     * @param $fields
     * @param $actions
     * @param bool $downloadable
     * @param array $searchableFields
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderQuery(Request $request, $sql, $fields, $actions, $downloadable = true, $searchableFields = array(), $options = [])
    {
        $list = $this->createList(new QueryList($sql, $fields, $actions, $downloadable, $searchableFields), $options);
        $this->addData('lista', $list->createView());
        if ($request->isMethod('POST')) {
            return $this->render('TrendixAdminBundle:Lista:lista.html.twig', $this->getData());
        } else {
            return $this->render('TrendixAdminBundle:Lista:index.html.twig', $this->getData());
        }
    }

    public static function getSubscribedServices()
    {
        $defaultServices = parent::getSubscribedServices();
        $customizedServices = [
            'admin.menu.generator' => '?'.MenuGenerator::class,
            'admin.lista.factory' => '?'.ListaFactory::class,
        ];

        $services = array_merge($defaultServices, $customizedServices);
        return $services;
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $this->addData($parameters);
        return parent::render($view, $this->getData(), $response);
    }

    /**
     * @return bool
     */
    public function hasParameter($key): bool
    {
        return $this->container->get('parameter_bag')->has($key);
    }
}
