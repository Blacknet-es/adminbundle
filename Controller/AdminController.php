<?php

namespace Trendix\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Trendix\AdminBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trendix\AdminBundle\Lista\NewsletterLista;
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

    /**
     * @Route("/newletter", name="admin_subscriber_index")
     */
    public function subscribersAction()
    {
        $lista = $this->createList(new NewsletterLista());

        $this->addData('lista', $lista->createView());
        $this->addData('title', 'Lista de suscriptores');

        return $this->render('TrendixAdminBundle:Lista:index.html.twig', $this->getData());
    }


    /**
     * @Route("/newletter/delete/{id}", name="admin_subscriber_delete")
     */
    public function subscribersDeleteAction($id)
    {
        $this->initializeEntityManager();
        $subscriber = $this->em->getRepository('TrendixAdminBundle:NewsletterSubscriber')->find($id);
        $this->em->delete($subscriber);
        $this->em->flush();

        return $this->redirectToRoute('admin_subscriber_index');
    }

    /**
     * @Route("/newsletter/export", name="admin_newsletter_export")
     */
    public function exportNewsletterAction()
    {
        $this->initializeEntityManager();

        $results = $this->em->getRepository('TrendixAdminBundle:NewsletterSubscriber')->findAll();
        $csv = '';
        foreach($results as $result) {
            $csv .= '' . $result->getEmail() . "\n";
        }

        // The csv service

        // Response with content
        $response = new Response($csv);

        // file prefix was injected
        $outFileName = 'export' . date('Ymd-Hi') . '.csv';

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));

        return $response;

    }

    /**
     * @Route("/upload_file", name="admin_upload_file")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadFileAction(Request $request)
    {
        try {
            $file = null;
            $name = null;
            $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/';
            if($request->getMethod() == 'POST') {
                $name = uniqid();
                foreach ($request->files as $uploadedFile) {
                    $file = $this->findUploadedFile($uploadedFile);
                    $name = $name . '_' . $file->getClientOriginalName();
                    $file = $file->move($dir, $name);
                }
            }
            return new JsonResponse(array('file' => $dir . $name));
        } catch (\Exception $e) {
            return new JsonResponse(array('Se ha producido un error: '.$e->getMessage()), 500);
        }

    }

    /**
     * Busca recursivamente el fichero subido dentro de un array asociativo
     * @param $uploadedFile
     * @return null
     */
    private function findUploadedFile($uploadedFile)
    {
        if ($uploadedFile instanceof UploadedFile) {
            return $uploadedFile;
        } elseif (is_array($uploadedFile)) {
            foreach ($uploadedFile as $uf) {
                return $this->findUploadedFile($uf);
            }
        }

        return null;
    }
}
