<?php

namespace Trendix\AdminBundle\Entity;

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

    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/myentity';
    }

    public function getAbsolutePath()
    {
        return null === $this->gallery ? null : $this->getUploadRootDir().'/'.$this->gallery;
    }

    public function getWebPath()
    {
        return null === $this->gallery ? null : '/'.$this->getUploadDir().'/'.$this->gallery;
    }
}