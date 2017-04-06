<?php

namespace Trendix\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Controller\SecurityController as BaseSecController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends BaseSecController
{

    public function loginAction(Request $request)
    {
        return parent::loginAction($request);
    }

    protected function renderLogin(array $data)
    {

        $requestAttributes = $this->container->get('request')->attributes;
        if ($requestAttributes->get('_route') == 'admin_login') {
            $template = 'TrendixAdminBundle:Security:admin_login.html.twig';
        } else {
            $template = 'FOSUserBundle:Security:login.html.twig';
        }
        $data['app_name'] = $this->getParameter('app_name');
        $data['registerButton'] = $this->getParameter('register_button');
        return $this->render($template, $data);
    }
}
