<?php

namespace Trendix\AdminBundle\Controller;

use Trendix\AdminBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trendix\AdminBundle\Lista\UserLista;


/**
 * Class AdminController
 * @package Trendix\AdminBundle\Controller
 * @Route("/admin")
 */
class AdminController extends BaseController
{
    /**
     * @Route("/", name="admin")
     */
    public function AdminIndexAction()
    {
        $lista = $this->createList(new UserLista());

        $this->addData('lista', $lista->createView());
        $this->addData('title', 'Lista de Usuarios');

        return $this->render('TrendixAdminBundle:Lista:index.html.twig', $this->getData());
    }

}
