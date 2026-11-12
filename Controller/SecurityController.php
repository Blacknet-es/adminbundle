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
        $requestAttributes = $this->container->get('request_stack')->getCurrentRequest()->attributes;
        if ($requestAttributes->get('_route') == 'admin_login') {
            if ($this->container->get('parameter_bag')->has('admin_login_template')) {
                $template = $this->getParameter('admin_login_template');
            } else {
                $template = 'TrendixAdminBundle:Security:admin_login.html.twig';
            }
        } else {
            if ($this->container->get('parameter_bag')->has('login_template')) {
                $template = $this->getParameter('login_template');
            } else {
                $template = 'FOSUserBundle:Security:login.html.twig';
            }
        }
        $data['app_name'] = $this->getParameter('app_name');
        if ($this->container->get('parameter_bag')->has('register_button')) {
            $data['registerButton'] = $this->getParameter('register_button');
        } else {
            $data['registerButton'] = false;
        }
        if ($this->container->get('parameter_bag')->has('login_title')) {
            $data['loginTitle'] = $this->getParameter('login_title');
        } else {
            $data['loginTitle'] = null;
        }

        if ($this->container->get('parameter_bag')->has('admin_primary_color')) {
            $data['admin_primary_color'] = $this->getParameter('admin_primary_color');
        }

        return $this->render($template, $data);
    }
}
