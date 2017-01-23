<?php

namespace Trendix\VoilaBundle\Entity;

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
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Provincias", inversedBy="municipios")
     * @ORM\JoinColumn(name="id_provincia", referencedColumnName="id")
     */
    private $provincia;

    /**
     * @var string
     *
     * @ORM\Column(name="municipio", type="string", length=255, nullable=false)
     */
    private $municipio;


}

