<?php

namespace Trendix\VoilaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provincias
 *
 * @ORM\Table(name="provincias", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class Provincias
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="provincia", type="string", length=255, nullable=false)
     */
    private $provincia;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_pais", type="integer", nullable=false)
     */
    private $idPais;

    /**
     * @var string
     *
     * @ORM\Column(name="iso", type="string", length=4, nullable=false)
     */
    private $iso;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Municipios", mappedBy="provincia")
     */
    private $municipios;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Provincias
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     * @return Provincias
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdPais()
    {
        return $this->idPais;
    }

    /**
     * @param int $idPais
     * @return Provincias
     */
    public function setIdPais($idPais)
    {
        $this->idPais = $idPais;
        return $this;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @param string $iso
     * @return Provincias
     */
    public function setIso($iso)
    {
        $this->iso = $iso;
        return $this;
    }

    /**
     * @return string
     */
    public function getMunicipios()
    {
        return $this->municipios;
    }

    /**
     * @param string $municipios
     * @return Provincias
     */
    public function setMunicipios($municipios)
    {
        $this->municipios = $municipios;
        return $this;
    }

}

