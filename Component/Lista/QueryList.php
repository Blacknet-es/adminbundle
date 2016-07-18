<?php


namespace Trendix\AdminBundle\Component\Lista;

use Trendix\AdminBundle\Component\Lista\ListaAbstractBuilder;
use Trendix\AdminBundle\Component\Lista\ListaAbstractType;

class QueryList extends ListaAbstractType
{
    private $sql;
    private $fields;
    private $downloadable;

    /**
     * QueryList constructor.
     * @param string $sql
     * @param array|string $fields
     * @param bool $downloadable
     */
    public function __construct($sql, $fields, $downloadable = false)
    {
        $this->sql = $sql;
        $this->fields = !is_array($fields) ? unserialize(trim($fields)) : $fields;
        $this->downloadable = $downloadable;
    }


    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        foreach($this->fields as $field) {
            $name = $field[0];
            $type = isset($field[1]) ? $field[1] : 'text';
            $label = isset($field[2]) ? $field[2] : $name;
            $builder->add($name, $type, array('label' => $label));
        }

        return $this;
    }

    public function getOptions()
    {
        $fields = serialize($this->fields);
        return array(
            'sql' => $this->sql,
            'downloadable' => $this->downloadable,
            'listCall' => $this->sql . '/;/'.$fields);
    }
}