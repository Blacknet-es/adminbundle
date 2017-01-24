<?php

namespace Trendix\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Municipios
 *
 * @ORM\Table(name="municipios", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class Municipios
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Trendix\AdminBundle\Entity\Provincias", inversedBy="municipios")
     * @ORM\JoinColumn(name="id_provincia", referencedColumnName="id")
     */
    private $provincia;

    /**
     * @var string
     *
     * @ORM\Column(name="municipio", type="string", length=255, nullable=false)
     */
    private $municipio;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Municipios
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * @param int $provincia
     * @return Municipios
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
        return $this;
    }

    /**
     * @return string
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * @param string $municipio
     * @return Municipios
     */
    public function setMunicipio($municipio)
    {
        $this->municipio = $municipio;
        return $this;
    }

}

