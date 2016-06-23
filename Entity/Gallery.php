<?php

namespace Trendix\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @ORM\Entity
 * @ORM\Table(name="gallery")
 */
class Gallery
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="gallery", type="array", nullable=true)
     */
    protected $gallery;

    /**
     * Gallery constructor.
     */
    public function __construct()
    {
        $this->gallery = array();
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
     * @return Gallery
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @param mixed $gallery
     * @return Gallery
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
        return $this;
    }


    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/images';
    }

    public function getAbsolutePath($index = 0)
    {
        return null === $this->gallery || count($this->gallery) == 0 ? null : $this->getUploadRootDir().'/'.$this->gallery[$index];
    }

    public function getWebPath($index = 0)
    {
        return null === $this->gallery || count($this->gallery) == 0 ? null : '/'.$this->getUploadDir().'/'.$this->gallery[$index];
    }

    function __toString()
    {
        return $this->getWebPath();
    }


}