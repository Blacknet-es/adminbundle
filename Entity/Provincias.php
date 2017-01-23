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


}

