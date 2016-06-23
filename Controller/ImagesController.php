<?php

namespace Trendix\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Trendix\AdminBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trendix\AdminBundle\Lista\UserLista;


/**
 * Class AdminController
 * @package Trendix\AdminBundle\Controller
 * @Route("/images")
 */
class ImagesController extends BaseController
{
    /**
     * @Route("/upload", name="trendix_image_upload")
     */
    public function uploadAction(Request $request)
    {
        $file = null;
        $name = null;
        $options = $request->request->get('config');
        $dir = $options['uploadConfig']['uploadUrl'];
        //$webDir = $options['uploadConfig']['webDir'];
        $error = false;
        if(!file_exists($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }
        if($request->getMethod() == 'POST') {
            $name = uniqid();
            foreach ($request->files as $uploadedFile) {
                $file = $uploadedFile[0];
                $name = $name . '_' . $file->getClientOriginalName();
                $file = $file->move($dir, $name);
            }
        } else {
            $error = true;
        }
        return new JsonResponse(array('file' => $name, 'error' => $error));
    }

    /**
     * @Route("/upload", name="trendix_image_upload")
     */
    public function cropAction(Request $request)
    {
        // Getting the configuration
        $options = $request->request->get('config');
        $imageName = $request->request->get('imageName');
        $params = $request->request->get('params');
        $dir = $options['uploadConfig']['uploadUrl'] . 'cropped/';
        // Getting the cropped image path:
        $originalFile = $options['uploadConfig']['uploadUrl'] . $imageName;
        $file = $dir . $imageName;

        // If the directory doesn't exist, create it:
        if(!file_exists($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        // If the original image doesn't exist, return an error:
        if(!file_exists($originalFile)) {
            return new JsonResponse(array('error' => 'Image not uploaded'));
        }

        // Else, proceed to crop the image:
        $imgInfo = getimagesize($originalFile);
        // Check the image extension:
        $file_ext = substr(strrchr($imageName, "."), 1);

        //Create the new image:
        $dst_r = ImageCreateTrueColor($params['w'], $params['h']);
        if($file_ext == 'jpg'){
            // Get original JPG image
            $img_r = imagecreatefromjpeg($originalFile);
        }
        elseif($file_ext == 'png'){
            // Get original PNG image with alpha channel
            $img_r = imagecreatefrompng($originalFile);
            imagealphablending($img_r, true);
            // And activate the alpha channel for the new image:
            imagesavealpha($dst_r, true);
        } else {
            return new JsonResponse(array('error' => 'Not supported format'));
        }
        // We set the background as transparent:
        $transparency = imagecolorallocatealpha($dst_r, 0, 0, 0, 127);
        imagefill($dst_r, 0, 0, $transparency);

        // We resample the image
        imagecopyresampled($dst_r, $img_r, 0, 0, $params['x'], $params['y'], $imgInfo[0], $imgInfo[1],
            $params['w'], $params['h']);
        imagesavealpha($dst_r, true); // save alphablending setting (important)
        // Finally, the image is stored
        imagepng($dst_r,$file);

        return new JsonResponse(array('file' => 'cropped/' . $imageName, 'error' => false));
    }
}
