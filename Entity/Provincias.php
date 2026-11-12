<?php

namespace Trendix\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provincias
 *
 * @ORM\Table(name="provincias")
 * @ORM\Entity
 */
class Provincias
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\OneToMany(targetEntity="Trendix\AdminBundle\Entity\Municipios", mappedBy="provincia")
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

    function __toString()
    {
        return $this->provincia;
    }

    public static function getIsosProvincias()
    {
        return array (
            array ('id' => "01", "name" => "ALAVA", 'iso' => 'VI'),
            array ('id' => "02", "name" => "ALBACETE", 'iso' => 'AB'),
            array ('id' => "03", "name" => "ALICANTE", 'iso' => 'A'),
            array ('id' => "04", "name" => "ALMERIA", 'iso' => 'AL'),
            array ('id' => "33", "name" => "ASTURIAS", 'iso' => 'O'),
            array ('id' => "05", "name" => "AVILA", 'iso' => 'AV'),
            array ('id' => "06", "name" => "BADAJOZ", 'iso' => 'BA'),
            array ('id' => "08", "name" => "BARCELONA", 'iso' => 'B'),
            array ('id' => "09", "name" => "BURGOS", 'iso' => 'BU'),
            array ('id' => "10", "name" => "CACERES", 'iso' => 'CC'),
            array ('id' => "11", "name" => "CADIZ", 'iso' => 'CA'),
            array ('id' => "39", "name" => "CANTABRIA", 'iso' => 'S'),
            array ('id' => "12", "name" => "CASTELLON", 'iso' => 'CS'),
            array ('id' => "51", "name" => "CEUTA", 'iso' => 'CE'),
            array ('id' => "13", "name" => "CIUDAD REAL", 'iso' => 'CR'),
            array ('id' => "14", "name" => "CORDOBA", 'iso' => 'CO'),
            array ('id' => "15", "name" => "A CORUÑA", 'iso' => 'C'),
            array ('id' => "16", "name" => "CUENCA", 'iso' => 'CU'),
            array ('id' => "17", "name" => "GIRONA", 'iso' => 'GI'),
            array ('id' => "18", "name" => "GRANADA", 'iso' => 'GR'),
            array ('id' => "19", "name" => "GUADALAJARA", 'iso' => 'GU'),
            array ('id' => "20", "name" => "GUIPUZCOA", 'iso' => 'SS'),
            array ('id' => "21", "name" => "HUELVA", 'iso' => 'H'),
            array ('id' => "22", "name" => "HUESCA", 'iso' => 'HU'),
            array ('id' => "07", "name" => "ILLES BALEARS", 'iso' => 'PM'),
            array ('id' => "23", "name" => "JAEN", 'iso' => 'J'),
            array ('id' => "24", "name" => "LEON", 'iso' => 'LE'),
            array ('id' => "25", "name" => "LLEIDA", 'iso' => 'L'),
            array ('id' => "27", "name" => "LUGO", 'iso' => 'LU'),
            array ('id' => "28", "name" => "MADRID", 'iso' => 'M'),
            array ('id' => "29", "name" => "MALAGA", 'iso' => 'MA'),
            array ('id' => "52", "name" => "MELILLA", 'iso' => 'ML'),
            array ('id' => "30", "name" => "MURCIA", 'iso' => 'MU'),
            array ('id' => "31", "name" => "NAVARRA", 'iso' => 'NA'),
            array ('id' => "32", "name" => "OURENSE", 'iso' => 'OR'),
            array ('id' => "34", "name" => "PALENCIA", 'iso' => 'P'),
            array ('id' => "35", "name" => "LAS PALMAS", 'iso' => 'GC'),
            array ('id' => "36", "name" => "PONTEVEDRA", 'iso' => 'PO'),
            array ('id' => "26", "name" => "LA RIOJA", 'iso' => 'LO'),
            array ('id' => "37", "name" => "SALAMANCA", 'iso' => 'SA'),
            array ('id' => "38", "name" => "SANTA CRUZ DE TENERIFE", 'iso' => 'TF'),
            array ('id' => "40", "name" => "SEGOVIA", 'iso' => 'SG'),
            array ('id' => "41", "name" => "SEVILLA", 'iso' => 'SE'),
            array ('id' => "42", "name" => "SORIA", 'iso' => 'SO'),
            array ('id' => "43", "name" => "TARRAGONA", 'iso' => 'T'),
            array ('id' => "44", "name" => "TERUEL", 'iso' => 'TE'),
            array ('id' => "45", "name" => "TOLEDO", 'iso' => 'TO'),
            array ('id' => "46", "name" => "VALENCIA", 'iso' => 'V'),
            array ('id' => "47", "name" => "VALLADOLID", 'iso' => 'VA'),
            array ('id' => "48", "name" => "VIZCAYA", 'iso' => 'BI'),
            array ('id' => "49", "name" => "ZAMORA", 'iso' => 'ZA'),
            array ('id' => "50", "name" => "ZARAGOZA", 'iso' => 'Z')
        );
    }

    public static function getCódigosPostalesEspecialesProvincias()
    {
        return array(
            "43421" => "08",
            "34492" => "09",
            "01427" => "09",
            "34815" => "09",
            "39232" => "09",
            "01211" => "09",
            "42142" => "09",
            "34260" => "09",
            "26212" => "09",
            "14449" => "13",
            "02636" => "16",
            "28190" => "19",
            "08717" => "19",
            "08619" => "25",
            "22583" => "25",
            "08281" => "25",
            "22584" => "25",
            "08697" => "25",
            "43449" => "25",
            "08289" => "25",
            "32151" => "27",
            "02438" => "30",
            "04690" => "30",
            "04647" => "30",
            "03657" => "30",
            "50686" => "31",
            "09471" => "40",
            "26127" => "42",
            "28310" => "45",
            "02215" => "46",
            "42269" => "50",
            "44591" => "50",
            "22808" => "50",
            "42220" => "50",
            "22806" => "50",
        );
    }

    public static function getIsoProvinciaPorId($code)
    {
        $id = $code;
        if(array_key_exists($code, self::cities())) {
            $id = self::cities()[$code];
        }
        $id = substr($id, 0, 2);
        $isoCodes = self::provinces();
        foreach ($isoCodes as $province) {
            if($province['id'] == $id) {
                return $province['iso'];
            }
        }
        return $code;
    }
}

