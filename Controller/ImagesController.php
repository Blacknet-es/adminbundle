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
        $thumbFile = $options['uploadConfig']['uploadUrl'] . 'thumb/' . $imageName;

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
        if($file_ext == 'jpg' || $file_ext == 'jpeg'){
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
        imagecopyresampled($dst_r, $img_r, 0, 0, $params['x'], $params['y'], $params['w'], $params['h'], $imgInfo[0],
            $imgInfo[1]);
        imagesavealpha($dst_r, true); // save alphablending setting (important)
        // Finally, the image is stored
        imagepng($dst_r,$file);
        $width = $options['uploadConfig']['thumbs']['maxWidth'];
        $height = $options['uploadConfig']['thumbs']['maxHeight'];

        // We also generate the thumbnail:
        $this->generateThumbnail($file, $thumbFile, $file_ext, $width, $height);

        return new JsonResponse(array('file' => 'cropped/' . $imageName, 'error' => false));
    }

    private function generateThumbnail($file, $dest, $ext, $alto = 150, $ancho = 150)
    {
        $img_info = getimagesize($file);
        //Depende de cadenas.php que se encuentra en la carpeta includes
        $tam_final = $this->getRatio($img_info, $ancho, $alto);
        //cabecera png de la imagen
        //detectamos el tipo mime y hacemos una copia de la imagen para trabajar con ella
        if($ext == 'jpeg' || $ext == 'jpg'){
            $img_r = imagecreatefromjpeg($file);
        }
        else if($ext == 'png'){
            $img_r = imagecreatefrompng($file);
            imagealphablending($img_r, true); // setting alpha blending on
            imagesavealpha($img_r, true); // save alphablending setting (important)
        }
        else {
            return false;
        }
        //Generamos una imagen transparente con el tamaño de la miniatura
        $dst_r = ImageCreateTrueColor($tam_final['0'], $tam_final['1']);
        $transparencia = imagecolorallocatealpha($dst_r, 0, 0, 0, 127);
        imagefill($dst_r, 0, 0, $transparencia);
        //metemos la redimension en la imagen de destino
        imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $tam_final['0'], $tam_final['1'], $img_info[0], $img_info[1]); //insertamos la imagen en la nueva con las coordenadas especificadas.
        imagesavealpha($dst_r, true); // save alphablending setting (important)
        //Mostramos a imagen
        imagepng($dst_r, $dest);
        //Liberamos la memoria
        imagedestroy($dst_r);
        return true;
    }

    private function getRatio($img_info, $ancho_max, $alto_max) {
        $proporcion = $img_info[0] / $img_info[1];
        if ($img_info[0] > $ancho_max || $img_info[1] > $alto_max) { //Comprobamos si supera los limites
            if ($ancho_max < ($alto_max * $proporcion)) { //La imagen es mas ancha que alta
                $alto_final = (int) ($ancho_max / $proporcion);
                $ancho_final = (int) $ancho_max;
            } else {// la imagen sigue siendo mas alta de lo permitido
                $ancho_final = (int) ($alto_max * $proporcion);
                $alto_final = (int) $alto_max;
            }
        } else {
            $alto_final = (int) $img_info[1];
            $ancho_final = (int) $img_info[0];
        }
        $img_final[0] = $ancho_final;
        $img_final[1] = $alto_final;
        return $img_final;
    }
}
