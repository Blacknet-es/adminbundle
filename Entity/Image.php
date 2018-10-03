<?php

namespace Trendix\AdminBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @ORM\Entity
 * @ORM\Table(name="image")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var string
     * @ORM\Column(name="original_image", type="string", length=255, nullable=true)
     */
    protected $originalImage;

    /**
     * @var string
     * @ORM\Column(name="upload_dir", type="string", length=255, nullable=true)
     */
    protected $uploadDir = 'uploads/images';

    /**
     * Image constructor.
     * @param $uploadDir
     */
    public function __construct($uploadDir = 'uploads/images')
    {
        $this->uploadDir = $uploadDir;
    }


    /**
     * @return string
     */
    public function getOriginalImage()
    {
        return $this->originalImage;
    }

    /**
     * @param string $originalImage
     * @return Image
     */
    public function setOriginalImage($originalImage)
    {
        $this->originalImage = $originalImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Image
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return Image
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }


    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return $this->uploadDir ? $this->uploadDir : 'uploads/images';
    }

    public function getAbsolutePath()
    {
        return null === $this->image ? null : $this->getUploadRootDir().'/'.$this->image;
    }

    public function getWebPath()
    {
        return null === $this->image ? '' : '/'.$this->getUploadDir().'/'.$this->image;
    }

    function __toString()
    {
        //return $this->image ? $this->image : '';
        return $this->getWebPath();
    }

}