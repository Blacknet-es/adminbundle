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
            if($this->container->hasParameter('admin_login_template')) {
                $template = $this->getParameter('admin_login_template');
            } else {
                $template = 'TrendixAdminBundle:Security:admin_login.html.twig';
            }
        } else {
            if($this->container->hasParameter('login_template')) {
                $template = $this->getParameter('login_template');
            } else {
                $template = 'FOSUserBundle:Security:login.html.twig';
            }
        }
        $data['app_name'] = $this->getParameter('app_name');
        if($this->container->hasParameter('register_button')) {
            $data['registerButton'] = $this->getParameter('register_button');
        } else {
            $data['registerButton'] = false;
        }
        if($this->container->hasParameter('login_title')) {
            $data['loginTitle'] = $this->getParameter('login_title');
        } else {
            $data['loginTitle'] = null;
        }

        if($this->container->hasParameter('admin_primary_color')) {
            $data['admin_primary_color'] = $this->getParameter('admin_primary_color');
        }

        if($this->getParameter('cookies_alert_enabled')) {
            $cookiesEnabled = $this->getParameter('cookies_alert_enabled');
            if($cookiesEnabled) {
                $data['cookieSettings'] = [
                    'cookies_alert_enabled' => $cookiesEnabled,
                    'cookies_alert_title' => $this->getParameter('cookies_alert_title'),
                    'cookies_alert_message' => $this->getParameter('cookies_alert_message'),
                    'cookies_alert_path' => $this->getParameter('cookies_alert_path'),
                    'cookies_alert_types' => $this->getParameter('cookies_alert_types')
                ];
            } else {
                $data['cookieSettings'] = [
                    'cookies_alert_enabled' => false
                ];
            }
        }

        return $this->render($template, $data);
    }
}
